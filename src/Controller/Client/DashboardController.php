<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client', name: 'client_dashboard')]
final class DashboardController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('client/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
