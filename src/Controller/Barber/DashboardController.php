<?php

namespace App\Controller\Barber;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/barber', name: 'barber_dashboard')]
final class DashboardController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('barber/dashboard.html.twig');
    }
}
