<?php

namespace App\Controller\API;

use App\Repository\BarberBarbershopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/barbers', name: 'api_barbers_')]
class BarberController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list() { /* retorna json lista barbeiros */ }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id) { /* json info barbeiro */ }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create() { /* cria barbeiro */ }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id) { /* atualiza barbeiro */ }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id) { /* remove barbeiro */ }
    #[Route('/by-barbershop/{barbershopId}', name: 'by_barbershop', methods: ['GET'])]
    public function getByBarbershop(int $barbershopId, BarberBarbershopRepository $repository)
    {
        $barberBarbershops = $repository->findBy(['id_barbershop' => $barbershopId]);

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
