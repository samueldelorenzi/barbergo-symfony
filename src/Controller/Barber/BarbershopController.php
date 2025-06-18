<?php

namespace App\Controller\Barber;

use App\Repository\BarberBarbershopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/barber/barbershop', name: 'barber_barbershop_')]
final class BarbershopController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        $barberBarbershops = $barberBarbershopRepository->findBy(['id_barber' => $this->getUser()->getId()]);
        $barbershop = !empty($barberBarbershops) ? $barberBarbershops[0]->getIdBarbershop() : null;
        return $this->render('barber/barbershop/dashboard.html.twig', [
            'barbershop' => $barbershop,
        ]);
    }
    #[Route('/view', name: 'view', methods: ['GET', 'POST'])]
    public function view(BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        return $this->render('barber/barbershop/view.html.twig', [
            'controller_name' => 'BarbershopController',
        ]);
    }
    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function manage(BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        return $this->render('barber/barbershop/edit.html.twig', [
            'controller_name' => 'BarbershopController',
        ]);
    }
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        return $this->render('barber/barbershop/create.html.twig', [
            'controller_name' => 'BarbershopController',
        ]);
    }
    #[Route('/join', name: 'join', methods: ['GET', 'POST'])]
    public function join(BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        return $this->render('barber/barbershop/join.html.twig', [
            'controller_name' => 'BarbershopController',
        ]);
    }
}
