<?php

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

    #[Route('/available_days/{barberId}', name: 'available_days', methods: ['GET'])]
    public function getAvailableDates(int $barberId): JsonResponse
    {
        $barber = $this->usersRepository->find($barberId);
        if (!$barber) {
            return new JsonResponse(['error' => 'Barber not found'], 404);
        }

        $schedules = $this->scheduleRepository->createQueryBuilder('s')
            ->where('s.id_barber = :barberId')
            ->setParameter('barberId', $barberId)
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult();

        if (!$schedules) {
            return new JsonResponse(['error' => 'No schedule found for this barber'], 404);
        }

        $latestSchedulePerDay = [];
        foreach ($schedules as $schedule) {
            $weekDay = (int) $schedule->getWeekDay();
            if (!isset($latestSchedulePerDay[$weekDay]) || $schedule->getId() > $latestSchedulePerDay[$weekDay]->getId()) {
                $latestSchedulePerDay[$weekDay] = $schedule;
            }
        }

        $activeDays = [];
        foreach ($latestSchedulePerDay as $weekDay => $schedule) {
            if ($schedule->isActive()) {
                $activeDays[$weekDay] = true;
            }
        }

        $dates = [];
        $dayNames = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        $today = new \DateTimeImmutable();
        $i = 0;

        while (count($dates) < 7) {
            $date = $today->modify("+$i days");
            $dayOfWeek = (int) $date->format('w');

            if (!empty($activeDays[$dayOfWeek])) {
                $dates[] = [
                    'date' => $date->format('Y-m-d'),
                    'dayName' => $dayNames[$dayOfWeek]
                ];
            }

            $i++;
        }

        return new JsonResponse($dates);
    }

    #[Route('/available_times/{barberId}', name: 'available_times', methods: ['GET'])]
    public function getAvailableTimes(int $barberId, Request $request): JsonResponse
    {
        $date = $request->query->get('date');
        $serviceId = $request->query->get('serviceId');

        if (!$date || !$serviceId) {
            return new JsonResponse(['error' => 'Missing required parameters: date and serviceId'], 400);
        }

        $barber = $this->usersRepository->find($barberId);
        $service = $this->serviceRepository->find($serviceId);

        if (!$barber || !$service) {
            return new JsonResponse(['error' => 'Invalid barber or service'], 404);
        }

        $serviceDuration = $service->getDurationMinutes();
        $weekday = (int) (new \DateTime($date))->format('w');

        $schedule = $this->scheduleRepository->createQueryBuilder('s')
            ->where('s.id_barber = :barber')
            ->andWhere('s.week_day = :week_day')
            ->setParameter('barber', $barber)
            ->setParameter('week_day', $weekday)
            ->orderBy('s.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$schedule || !$schedule->isActive()) {
            return new JsonResponse([]); // sem agenda ou inativo
        }

        $start = new \DateTime($date . ' ' . $schedule->getStartTime()->format('H:i:s'));
        $end = new \DateTime($date . ' ' . $schedule->getEndTime()->format('H:i:s'));

        $lunchStart = new \DateTime($date . ' 12:00:00');
        $lunchEnd = new \DateTime($date . ' 13:30:00');

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

        $now = new \DateTime();
        $times = [];
        while ($start <= $end) {
            $slot = $start->format('H:i');
            $slotEnd = (clone $start)->modify("+{$serviceDuration} minutes");

            if ($slotEnd > $end) {
                break;
            }

            if ($start < $now && $date === $now->format('Y-m-d')) {
                $start->modify('+30 minutes');
                continue;
            }

            if ($start >= $lunchStart && $start < $lunchEnd) {
                $start->modify('+30 minutes');
                continue;
            }

            if (!in_array($slot, $bookedTimes)) {
                $times[] = $slot;
            }

            $start->modify('+30 minutes');
        }

        return new JsonResponse($times);
    }
}
