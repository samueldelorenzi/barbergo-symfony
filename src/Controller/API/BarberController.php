<?php

namespace App\Controller\API;

use App\Repository\BarberBarbershopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/barbers', name: 'api_barbers_')]
class BarberController extends AbstractController
{
    private BarberBarbershopRepository $repository;

    public function __construct(BarberBarbershopRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Route('/by-barbershop/{barbershopId}', name: 'by_barbershop', methods: ['GET'])]
    public function getByBarbershop(int $barbershopId): JsonResponse
    {
        $barberBarbershops = $this->repository->findBy(['id_barbershop' => $barbershopId]);

        $barbers = [];
        foreach ($barberBarbershops as $relation) {
            $barber = $relation->getIdBarber();
            if ($barber->isActive()) {
                $barbers[] = [
                    'id' => $barber->getId(),
                    'name' => $barber->getName(),
                ];
            }
        }

        return new JsonResponse($barbers);
    }
}
