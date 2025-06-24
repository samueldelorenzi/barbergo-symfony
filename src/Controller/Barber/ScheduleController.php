<?php

namespace App\Controller\Barber;

use App\Entity\Schedule;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/barber/schedule', name: 'barber_schedule_')]
final class ScheduleController extends AbstractController
{
    private EntityManagerInterface $em;
    private ScheduleRepository $scheduleRepository;
    private $user;

    public function __construct(EntityManagerInterface $em, ScheduleRepository $scheduleRepository)
    {
        $this->em = $em;
        $this->scheduleRepository = $scheduleRepository;
    }

    private function getCurrentUser()
    {
        if (!$this->user) {
            $this->user = $this->getUser();
        }
        return $this->user;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getCurrentUser();
        $schedules = [];

        for ($i = 0; $i <= 6; $i++) {
            $schedule = $this->scheduleRepository->createQueryBuilder('s')
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
    public function save(Request $request): Response
    {
        $referer = $request->headers->get('referer');
        $data = $request->request->all();

        if (!isset($data['schedule']) || !is_array($data['schedule'])) {
            $this->addFlash('danger', 'Erro ao atualizar horários!');
            return $this->redirect($referer);
        }

        $schedulesData = $data['schedule'];
        $user = $this->getCurrentUser();

        foreach ($schedulesData as $item) {
            $startTime = new \DateTime($item['start']);
            $endTime = new \DateTime($item['end']);
            if ($startTime >= $endTime) {
                $this->addFlash('danger', "O horário de início não pode ser maior ou igual ao horário de término. Dia: {$item['day']}, Início: {$item['start']}, Fim: {$item['end']}");
                return $this->redirect($referer);
            }

            $schedule = new Schedule();
            $schedule->setIdBarber($user);
            $schedule->setWeekDay((string)$item['day']);
            $schedule->setStartTime($startTime);
            $schedule->setEndTime($endTime);
            $schedule->setActive($item['active']);

            $this->em->persist($schedule);
        }

        $this->em->flush();
        $this->addFlash('success', 'Horários atualizados com sucesso!');

        return $this->redirect($referer);
    }
}
