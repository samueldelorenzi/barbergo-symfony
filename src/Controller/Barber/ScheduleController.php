<?php

namespace App\Controller\Barber;

use App\Entity\BarberBarbershop;
use App\Entity\Barbershop;
use App\Entity\JoinRequest;
use App\Entity\Schedule;
use App\Entity\User;
use App\Form\BarbershopTypeForm;
use App\Repository\BarberBarbershopRepository;
use App\Repository\BarbershopRepository;
use App\Repository\JoinRequestRepository;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/barber/schedule', name: 'barber_schedule_')]
final class ScheduleController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ScheduleRepository $scheduleRepository): Response
    {
        $user = $this->getUser();
        $schedules = [];
        for ($i = 0; $i <= 6; $i++) {
            $schedule = $scheduleRepository->createQueryBuilder('s')
                ->where('s.id_barber = :barber')
                ->andWhere('s.week_day = :day')
                ->setParameter('barber', $user)
                ->setParameter('day', (string)$i)
                ->orderBy('s.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($schedule) {
                $schedules[] = $schedule;
            }
        }
        return $this->render('barber/schedule/index.html.twig', [
            'user' => $user,
            'existingSchedules' => $schedules
        ]);
    }
    #[Route('', name: 'save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em): Response
    {
        $data = $request->request->all();

        if (!isset($data['schedule']) || !is_array($data['schedule'])) {
            $this->addFlash('danger', 'Erro ao atualizar horários!');
            return $this->redirect($request->headers->get('referer'));
        }

        $schedulesData = $data['schedule'];
        $user = $this->getUser();

        foreach ($schedulesData as $item) {
            $startTime = new \DateTime($item['start']);
            $endTime = new \DateTime($item['end']);
            if ($startTime >= $endTime) {
                $this->addFlash('danger', "O horário de início não pode ser maior ou igual ao horário de término. Dia: {$item['day']}, Início: {$item['start']}, Fim: {$item['end']}");
                return $this->redirect($request->headers->get('referer'));
            }

            $schedule = new Schedule();
            $schedule->setIdBarber($user);
            $schedule->setWeekDay((string)$item['day']);
            $schedule->setStartTime(new \DateTime($item['start']));
            $schedule->setEndTime(new \DateTime($item['end']));
            $schedule->setActive($item['active']);

            $em->persist($schedule);
        }

        $em->flush();
        $this->addFlash('success', 'Horários atualizados com sucesso!');

        return $this->redirect($request->headers->get('referer'));
    }
}
