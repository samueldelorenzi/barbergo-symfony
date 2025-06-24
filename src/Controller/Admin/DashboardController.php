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
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('', name: '')]
    public function index(): Response
    {
        $userRepo = $this->em->getRepository(User::class);
        $barbershopRepo = $this->em->getRepository(Barbershop::class);
        $appointmentRepo = $this->em->getRepository(Appointment::class);

        $users = $userRepo->findAll();
        $barbershops = $barbershopRepo->findAll();
        $appointments = $appointmentRepo->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => count($users),
            'barbershopCount' => count($barbershops),
            'appointmentsCount' => count($appointments),
        ]);
    }
}
