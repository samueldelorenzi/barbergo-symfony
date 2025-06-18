<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\Barbershop;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/dashboard', name: 'admin_dashboard')]
class DashboardController extends AbstractController
{
    #[Route('', name: '')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        $barbershops = $em->getRepository(Barbershop::class)->findAll();
        $appointments = $em->getRepository(Appointment::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => count($users),
            'barbershopCount' => count($barbershops),
            'appointmentsCount' => count($appointments)
        ]);
    }
}
