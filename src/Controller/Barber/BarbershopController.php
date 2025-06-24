<?php

namespace App\Controller\Barber;

use App\Entity\BarberBarbershop;
use App\Entity\Barbershop;
use App\Entity\JoinRequest;
use App\Entity\Service;
use App\Entity\User;
use App\Form\BarbershopTypeForm;
use App\Form\ServiceTypeForm;
use App\Repository\BarberBarbershopRepository;
use App\Repository\BarbershopRepository;
use App\Repository\JoinRequestRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/barber/barbershop', name: 'barber_barbershop_')]
final class BarbershopController extends AbstractController
{
    private BarberBarbershopRepository $barberBarbershopRepository;
    private BarbershopRepository $barbershopRepository;
    private JoinRequestRepository $joinRequestRepository;
    private ServiceRepository $serviceRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        BarberBarbershopRepository $barberBarbershopRepository,
        BarbershopRepository $barbershopRepository,
        JoinRequestRepository $joinRequestRepository,
        ServiceRepository $serviceRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->barberBarbershopRepository = $barberBarbershopRepository;
        $this->barbershopRepository = $barbershopRepository;
        $this->joinRequestRepository = $joinRequestRepository;
        $this->serviceRepository = $serviceRepository;
        $this->entityManager = $entityManager;
    }

    private function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();
        return $user;
    }

    private function getBarbershopByCurrentUser(): ?Barbershop
    {
        $barberBarbershops = $this->barberBarbershopRepository->findBy(['id_barber' => $this->getCurrentUser()->getId()]);
        return !empty($barberBarbershops) ? $barberBarbershops[0]->getIdBarbershop() : null;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $barbershop = $this->getBarbershopByCurrentUser();
        $owner = null;

        if ($barbershop) {
            $owner = $barbershop->getCreatedBy();
            if ($owner->getId() !== $this->getCurrentUser()->getId()) {
                $owner = null;
            }
        }

        return $this->render('barber/barbershop/dashboard.html.twig', [
            'barbershop' => $barbershop,
            'owner' => $owner,
        ]);
    }

    #[Route('/view', name: 'view', methods: ['GET', 'POST'])]
    public function view(Request $request): Response
    {
        $currentUser = $this->getCurrentUser();

        $relations = $this->barberBarbershopRepository->findBy(['id_barber' => $currentUser->getId()]);

        if (empty($relations)) {
            $this->addFlash('danger', 'Você não está vinculado a nenhuma barbearia.');
            return $this->redirectToRoute('barber_dashboard');
        }

        $barbershop = $relations[0]->getIdBarbershop();

        $allRelations = $this->barberBarbershopRepository->findBy(['id_barbershop' => $barbershop->getId()]);
        $barbers = [];
        $seenIds = [];

        foreach ($allRelations as $relation) {
            $barber = $relation->getIdBarber();
            if (!in_array($barber->getId(), $seenIds, true)) {
                $seenIds[] = $barber->getId();
                $barbers[] = $barber;
            }
        }

        $owner = $barbershop->getCreatedBy();
        $isOwner = $owner->getId() === $currentUser->getId();

        return $this->render('barber/barbershop/view.html.twig', [
            'barbershop' => $barbershop,
            'barbers' => $barbers,
            'owner' => $owner,
            'isowner' => $isOwner,
        ]);
    }

    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function manage(Request $request): Response
    {
        $barbershop = $this->barbershopRepository->findOneBy(['created_by' => $this->getCurrentUser()->getId()]);
        $joinRequests = $this->joinRequestRepository->findBy([
            'barbershop' => $barbershop,
            'status' => 'pending',
        ]);

        $form = $this->createForm(BarbershopTypeForm::class, $barbershop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Barbearia atualizada com sucesso!');
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('barber/barbershop/edit.html.twig', [
            'form' => $form,
            'barbershop' => $barbershop,
            'join_requests' => $joinRequests,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $barbershop = new Barbershop();

        $form = $this->createForm(BarbershopTypeForm::class, $barbershop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getCurrentUser();

            $barbershop->setCreatedBy($user);
            $barbershop->setCreatedAt(new \DateTimeImmutable());
            $barbershop->setActive(true);

            $this->entityManager->persist($barbershop);

            $barberBarbershop = new BarberBarbershop();
            $barberBarbershop->setIdBarber($user);
            $barberBarbershop->setIdBarbershop($barbershop);

            $this->entityManager->persist($barberBarbershop);

            $this->entityManager->flush();

            $this->addFlash('success', 'Barbearia criada com sucesso!');

            return $this->redirectToRoute('barber_barbershop_view', ['id' => $barbershop->getId()]);
        }

        return $this->render('barber/barbershop/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/join', name: 'join', methods: ['GET', 'POST'])]
    public function join(): Response
    {
        $user = $this->getCurrentUser();
        $barberBarbershops = $this->barberBarbershopRepository->findBy(['id_barber' => $user->getId()]);

        if (!empty($barberBarbershops)) {
            return $this->redirectToRoute('barber_barbershop_index');
        }

        $barbershops = $this->barbershopRepository->findAll();

        return $this->render('barber/barbershop/join.html.twig', [
            'barbershops' => $barbershops,
        ]);
    }

    #[Route('/join/{id}', name: 'join_by_id', methods: ['POST'])]
    public function join_by_id(int $id, Request $request): Response
    {
        $barbershop = $this->barbershopRepository->find($id);

        if (!$barbershop) {
            $this->addFlash('danger', 'Barbearia não encontrada.');
            return $this->redirectToRoute('barber_barbershop_join');
        }

        $user = $this->getCurrentUser();

        $existing = $this->entityManager->getRepository(JoinRequest::class)->findOneBy([
            'user' => $user,
            'barbershop' => $barbershop,
            'status' => 'pending',
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Você já enviou um pedido para essa barbearia.');
            return $this->redirect($request->headers->get('referer'));
        }

        $joinRequest = new JoinRequest();
        $joinRequest->setUser($user);
        $joinRequest->setBarbershop($barbershop);

        $this->entityManager->persist($joinRequest);
        $this->entityManager->flush();

        $this->addFlash('success', 'Pedido enviado com sucesso! Aguarde a aprovação.');

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/join/rejected/{id}', name: 'join_by_id_rejected', methods: ['POST'])]
    public function join_by_id_rejected(int $id, Request $request): Response
    {
        $joinRequest = $this->entityManager->getRepository(JoinRequest::class)->find($id);

        if (!$joinRequest) {
            $this->addFlash('danger', 'Pedido não encontrado.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
        }

        $joinRequest->setStatus('rejected');
        $this->entityManager->persist($joinRequest);
        $this->entityManager->flush();

        $this->addFlash('success', 'Pedido rejeitado com sucesso.');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
    }

    #[Route('/join/approved/{id}', name: 'join_by_id_approved', methods: ['POST'])]
    public function join_by_id_approved(int $id, Request $request): Response
    {
        $joinRequest = $this->entityManager->getRepository(JoinRequest::class)->find($id);

        if (!$joinRequest) {
            $this->addFlash('danger', 'Pedido não encontrado.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
        }

        $barbershop = $joinRequest->getBarbershop();
        $barber = $joinRequest->getUser();

        $barberBarbershop = new BarberBarbershop();
        $barberBarbershop->setIdBarber($barber);
        $barberBarbershop->setIdBarbershop($barbershop);

        $this->entityManager->persist($barberBarbershop);

        $joinRequest->setStatus('approved');
        $this->entityManager->persist($joinRequest);

        $this->entityManager->flush();

        $this->addFlash('success', 'Pedido aceito com sucesso.');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
    }

    #[Route('/services', name: 'services_index', methods: ['GET'])]
    public function services_index(): Response
    {
        $barbershop = $this->getBarbershopByCurrentUser();
        if (!$barbershop) {
            $this->addFlash('danger', 'Barbearia não encontrada para o usuário atual.');
            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        $services = $this->serviceRepository->findBy(['id_barbershop' => $barbershop, 'active' => true]);

        return $this->render('barber/barbershop/services/index.html.twig', [
            'services' => $services,
            'barbershop' => $barbershop,
        ]);
    }

    #[Route('/services/new', name: 'services_new', methods: ['GET', 'POST'])]
    public function services_new(Request $request): Response
    {
        $barbershop = $this->getBarbershopByCurrentUser();
        if (!$barbershop) {
            $this->addFlash('danger', 'Barbearia não encontrada para o usuário atual.');
            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        $service = new Service();
        $service->setIdBarbershop($barbershop);
        $service->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ServiceTypeForm::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($service);
            $this->entityManager->flush();

            $this->addFlash('success', 'Serviço criado com sucesso!');

            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        return $this->render('barber/barbershop/services/create.html.twig', [
            'form' => $form,
            'barbershop' => $barbershop,
        ]);
    }

    #[Route('/services/edit/{id}', name: 'services_edit', methods: ['GET', 'POST'])]
    public function services_edit(Request $request, int $id): Response
    {
        $barbershop = $this->getBarbershopByCurrentUser();
        if (!$barbershop) {
            $this->addFlash('danger', 'Barbearia não encontrada para o usuário atual.');
            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        $service = $this->serviceRepository->find($id);

        if (!$service || $service->getIdBarbershop()->getId() !== $barbershop->getId()) {
            $this->addFlash('danger', 'Serviço não encontrado para esta barbearia.');
            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        $form = $this->createForm(ServiceTypeForm::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Serviço atualizado com sucesso!');

            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        return $this->render('barber/barbershop/services/edit.html.twig', [
            'form' => $form,
            'barbershop' => $barbershop,
            'service' => $service,
        ]);
    }

    #[Route('/services/delete/{id}', name: 'services_delete', methods: ['GET', 'POST'])]
    public function services_delete(Request $request, int $id): Response
    {
        $barbershop = $this->getBarbershopByCurrentUser();
        if (!$barbershop) {
            $this->addFlash('danger', 'Barbearia não encontrada para o usuário atual.');
            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        $service = $this->serviceRepository->find($id);

        if (!$service || $service->getIdBarbershop()->getId() !== $barbershop->getId()) {
            $this->addFlash('danger', 'Serviço não encontrado para esta barbearia.');
            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        if ($this->isCsrfTokenValid('delete_service_'.$service->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($service);
            $this->entityManager->flush();
            $this->addFlash('success', 'Serviço removido com sucesso!');
        }
        else {
            $this->addFlash('danger', 'Token inválido.');
        }

        return $this->redirectToRoute('barber_barbershop_services_index');
    }
}
