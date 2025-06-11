<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Barbershop;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        $barbershops = $em->getRepository(Barbershop::class)->findAll();
        $appointments = $em->getRepository(Appointment::class)->findAll();

        return $this->render('dashboard/index.html.twig', [
            'userCount' => count($users),
            'barbershopCount' => count($barbershops),
            'appointmentsCount' => count($appointments)
        ]);
    }
}
