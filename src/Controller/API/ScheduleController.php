<?php
// src/Controller/HelperController.php

namespace App\Controller\API;

use App\Repository\AppointmentsRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/schedules', name: 'api_schedules_')]
class ScheduleController extends AbstractController
{
    private UserRepository $usersRepository;
    private ScheduleRepository $scheduleRepository;
    private AppointmentsRepository $appointmentsRepository;
    private ServiceRepository $serviceRepository;

    public function __construct(
        UserRepository $usersRepository,
        ScheduleRepository $scheduleRepository,
        AppointmentsRepository $appointmentsRepository,
        ServiceRepository $serviceRepository
    ) {
        $this->usersRepository = $usersRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->serviceRepository = $serviceRepository;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list()
    {
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id)
    {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create()
    {
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id)
    {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id)
    {
    }

    #[Route('/available_days/{barberId}', name: 'available_days', methods: ['GET'])]
    public function getAvailableDates(int $barberId): JsonResponse
    {
        $barber = $this->usersRepository->find($barberId);
        if (!$barber) {
            return new JsonResponse(['error' => 'Barber not found'], 404);
        }

        $schedules = $this->scheduleRepository->findBy(['id_barber' => $barber]);
        if (!$schedules) {
            return new JsonResponse(['error' => 'No schedule found for this barber'], 404);
        }

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

    #[Route('/available_times/{barberId}', name: 'available_times', methods: ['GET'])]
    public function getAvailableTimes(int $barberId, Request $request): JsonResponse
    {
        $date = $request->query->get('date');
        $serviceId = $request->query->get('serviceId');

        if (!$date || !$serviceId) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        $barber = $this->usersRepository->find($barberId);
        $service = $this->serviceRepository->find($serviceId);

        if (!$barber || !$service) {
            return new JsonResponse(['error' => 'Invalid barber or service'], 404);
        }

        $serviceDuration = $service->getDurationMinutes();
        $weekday = (new \DateTime($date))->format('N');

        $schedule = $this->scheduleRepository->findOneBy([
            'id_barber' => $barber,
            'week_day' => $weekday
        ]);

        if (!$schedule) {
            return new JsonResponse([]);
        }

        $start = new \DateTime($date . ' ' . $schedule->getStartTime()->format('H:i:s'));
        $end = new \DateTime($date . ' ' . $schedule->getEndTime()->format('H:i:s'));

        $appointments = $this->appointmentsRepository->createQueryBuilder('a')
            ->where('a.id_barber = :barber')
            ->andWhere('a.appointment_date = :date')
            ->setParameter('barber', $barber)
            ->setParameter('date', new \DateTime($date))
            ->getQuery()
            ->getResult();

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

            $start->modify('+30 minutes');
        }

        return new JsonResponse($times);
    }
}
