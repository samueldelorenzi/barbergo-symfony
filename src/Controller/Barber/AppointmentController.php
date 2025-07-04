<?php

namespace App\Controller\Barber;

use App\Entity\Appointment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/barber/appointment', name: 'barber_appointment_')]
final class AppointmentController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function my_clients_appointments(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $appointments = $em->createQueryBuilder()
            ->select('a')
            ->from(Appointment::class, 'a')
            ->where('a.id_barber = :barber')
            ->setParameter('barber', $user)
            ->orderBy('a.appointment_date', 'ASC')
            ->addOrderBy('a.appointment_time', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('barber/appointment/list.html.twig', [
            'appointments' => $appointments,
            'user' => $user,
        ]);
    }
}
