<?php

namespace App\Controller\Barber;

use App\Repository\AppointmentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/barber', name: 'barber_dashboard')]
final class DashboardController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(AppointmentsRepository $appointmentsRepository): Response
    {
        $user = $this->getUser();
        $appointments = ($appointmentsRepository->findBy(['id_barber' => $user]));

        $lastMonth = new \DateTime('-1 month');

        $appointmentsLastMonth = $appointmentsRepository->createQueryBuilder('a')
            ->where('a.id_barber = :barber')
            ->andWhere('a.appointment_date >= :lastMonth')
            ->setParameter('barber', $user)
            ->setParameter('lastMonth', $lastMonth)
            ->orderBy('a.appointment_date', 'DESC')
            ->getQuery()
            ->getResult();

        $estimatedRevenue = 0.0;

        foreach ($appointments as $appointment) {
            $service = $appointment->getIdService();

            if ($service && $service->getPrice() !== null) {
                $estimatedRevenue += (float) $service->getPrice();
            }
        }

        $appointmentsNumber = count($appointments);
        return $this->render('barber/dashboard.html.twig', [
            'appointmentsNumber' => $appointmentsNumber,
            'appointmentsLastMonth' => count($appointmentsLastMonth),
            'estimatedRevenue' => $estimatedRevenue,
        ]);
    }
}
