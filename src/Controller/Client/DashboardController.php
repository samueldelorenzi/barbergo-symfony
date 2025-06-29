<?php

namespace App\Controller\Client;

use App\Repository\AppointmentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client', name: 'client_dashboard')]
final class DashboardController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(AppointmentsRepository $appointmentsRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $appointmentsNumber = count($appointmentsRepository->findBy(['id_client' => $user]));

        $favoriteBarberResult = $em->createQuery(
            'SELECT b.name, COUNT(a.id) AS total
             FROM App\Entity\Appointment a
             JOIN a.id_barber b
             WHERE a.id_client = :client
             GROUP BY b.id
             ORDER BY total DESC'
        )
            ->setParameter('client', $user)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        $now = new \DateTime();
        $nextAppointment = $em->createQuery(
            'SELECT a
             FROM App\Entity\Appointment a
             WHERE a.id_client = :client
             AND a.appointment_date > :today
             OR (a.appointment_date = :today AND a.appointment_time > :nowTime)
             ORDER BY a.appointment_date ASC, a.appointment_time ASC'
        )
            ->setParameter('client', $user)
            ->setParameter('today', $now->format('Y-m-d'))
            ->setParameter('nowTime', $now->format('H:i:s'))
            ->setMaxResults(1)
            ->getOneOrNullResult();

        $nextAppointmentDate = $nextAppointment ? $nextAppointment->getAppointmentDate()->format('d/m/Y') : null;

        $favoriteBarber = $favoriteBarberResult['name'] ?? null;

        return $this->render('client/dashboard.html.twig', [
            'appointmentsNumber' => $appointmentsNumber,
            'favoriteBarber' => $favoriteBarber,
            'user' => $user,
            'nextAppointment' => $nextAppointmentDate,
        ]);
    }
}
