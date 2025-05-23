<?php

namespace App\Controller;

use App\Entity\Barbershop;
use App\Entity\Users;
use App\Entity\Appointments;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(Users::class)->findAll();
        $barbershops = $em->getRepository(Barbershop::class)->findAll();
        $appointments = $em->getRepository(Appointments::class)->findAll();

        return $this->render('dashboard/index.html.twig', [
            'userCount' => count($users),
            'barbershopCount' => count($barbershops),
            'appointmentsCount' => count($appointments)
        ]);
    }
}
