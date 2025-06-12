<?php
// src/Controller/HelperController.php

namespace App\Controller;

use App\Repository\AppointmentsRepository;
use App\Repository\BarberBarbershopRepository;
use App\Repository\BarbershopRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HelperController extends AbstractController
{
    #[Route('/get-barbers', name: 'get_barbers_by_barbershop', methods: ['GET'])]
    public function getBarbers(Request $request, BarberBarbershopRepository $repository): JsonResponse
    {
        $barbershopId = $request->query->get('barbershopId');
        $barberBarbershops = $repository->findBy(['id_barbershop' => $barbershopId]);

        $barbers = [];
        foreach ($barberBarbershops as $relation) {
            $barber = $relation->getIdBarber();
            if ($barber->isActive()) {
                $barbers[] = [
                    'id' => $barber->getId(),
                    'name' => $barber->getName(),
                ];
            }
        }

        return new JsonResponse($barbers);
    }

    #[Route('/get-service', name: 'get_services_by_barbershop', methods: ['GET'])]
    public function getService(Request $request, ServiceRepository $serviceRepository, BarbershopRepository $barbershopRepository): JsonResponse
    {
        $barbershopId = $request->query->get('barbershopId');
        if (!$barbershopId) {
            return new JsonResponse(['error' => 'Missing barbershopId parameter'], 400);
        }

        $barbershop = $barbershopRepository->find($barbershopId);
        if (!$barbershop) {
            return new JsonResponse(['error' => 'Barbershop not found'], 404);
        }

        $services = $serviceRepository->findBy(['id_barbershop' => $barbershop]);

        $result = array_map(fn($service) => [
            'id' => $service->getId(),
            'name' => $service->getName(),
            'duration_minutes' => $service->getDurationMinutes(),
            'price' => $service->getPrice(),
        ], $services);

        return new JsonResponse($result);
    }

    #[Route('/get-available-dates', name: 'get_available_dates', methods: ['GET'])]
    public function getAvailableDates(
        Request            $request,
        UserRepository     $usersRepository,
        ScheduleRepository $scheduleRepository
    ): JsonResponse
    {
        $barberId = $request->query->get('barberId');

        if (!$barberId) {
            return new JsonResponse(['error' => 'Missing barberId parameter'], 400);
        }

        $barber = $usersRepository->find($barberId);
        if (!$barber) {
            return new JsonResponse(['error' => 'Barber not found'], 404);
        }

        $schedules = $scheduleRepository->findBy(['id_barber' => $barber]);
        if (!$schedules) {
            return new JsonResponse(['error' => 'No schedule found for this barber'], 404);
        }

        // Generate dates for next 7 days
        $dates = [];
        $dayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        $today = new \DateTimeImmutable();

        for ($i = 0; $i < 7; $i++) {
            $date = $today->modify("+$i days");
            $dayOfWeek = (int)$date->format('w');

            foreach ($schedules as $schedule) {
                if ((int)$schedule->getWeekDay() === (($dayOfWeek === 0) ? 7 : $dayOfWeek)) {
                    $dates[] = [
                        'date' => $date->format('Y-m-d'),
                        'dayName' => $dayNames[$dayOfWeek]
                    ];
                    break;
                }
            }
        }

        return new JsonResponse($dates);
    }

    #[Route('/get-available-times', name: 'get_available_times', methods: ['GET'])]
    public function getAvailableTimes(
        Request                $request,
        UserRepository         $usersRepository,
        AppointmentsRepository $appointmentsRepository,
        ScheduleRepository     $scheduleRepository,
        ServiceRepository      $serviceRepository
    ): JsonResponse
    {
        try {
            $barberId = $request->query->get('barberId');
            $date = $request->query->get('date');
            $serviceId = $request->query->get('serviceId');

            if (!$barberId || !$date || !$serviceId) {
                return new JsonResponse(['error' => 'Missing parameters'], 400);
            }

            $barber = $usersRepository->find($barberId);
            $service = $serviceRepository->find($serviceId);

            if (!$barber || !$service) {
                return new JsonResponse(['error' => 'Invalid barber or service'], 404);
            }

            $serviceDuration = $service->getDurationMinutes();
            $weekday = (new \DateTime($date))->format('N');

            $schedule = $scheduleRepository->findOneBy([
                'id_barber' => $barber,
                'week_day' => $weekday
            ]);

            if (!$schedule) {
                return new JsonResponse([]);
            }

            $start = new \DateTime($date . ' ' . $schedule->getStartTime()->format('H:i:s'));
            $end = new \DateTime($date . ' ' . $schedule->getEndTime()->format('H:i:s'));

            $appointments = $appointmentsRepository->createQueryBuilder('a')
                ->where('a.id_barber = :barber')
                ->andWhere('a.appointment_date = :date')
                ->setParameter('barber', $barber)
                ->setParameter('date', new \DateTime($date))
                ->getQuery()
                ->getResult();

            // Pega somente os horários (HH:MM) já agendados
            $bookedTimes = array_map(
                fn($appt) => $appt->getAppointmentTime()->format('H:i'),
                $appointments
            );

            $times = [];
            while ($start <= $end) {
                $slot = $start->format('H:i');
                $slotEnd = (clone $start)->modify("+{$serviceDuration} minutes");

                if ($slotEnd > $end) break;

                if (!in_array($slot, $bookedTimes)) {
                    $times[] = $slot;
                }

                $start->modify('+30 minutes'); // pode ser ajustado conforme a granularidade que quiser
            }

            return new JsonResponse($times);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}
