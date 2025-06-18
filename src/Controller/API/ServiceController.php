<?php

namespace App\Controller\API;

use App\Repository\BarbershopRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/services', name: 'api_services_')]
class ServiceController extends AbstractController
{
    private ServiceRepository $serviceRepository;
    private BarbershopRepository $barbershopRepository;

    public function __construct(
        ServiceRepository $serviceRepository,
        BarbershopRepository $barbershopRepository
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->barbershopRepository = $barbershopRepository;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse(['services' => []]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        return new JsonResponse([]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        return new JsonResponse([]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id): JsonResponse
    {
        return new JsonResponse([]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return new JsonResponse([]);
    }

    #[Route('/barbershop/{barbershopId}', name: 'get_services_by_barbershop', methods: ['GET'])]
    public function getServiceByBarbershop(int $barbershopId): JsonResponse
    {
        $barbershop = $this->barbershopRepository->find($barbershopId);
        if (!$barbershop) {
            return new JsonResponse(['error' => 'Barbershop not found'], 404);
        }

        $services = $this->serviceRepository->findBy(['id_barbershop' => $barbershop]);

        $result = array_map(fn($service) => [
            'id' => $service->getId(),
            'name' => $service->getName(),
            'duration_minutes' => $service->getDurationMinutes(),
            'price' => $service->getPrice(),
        ], $services);

        return new JsonResponse($result);
    }
}
