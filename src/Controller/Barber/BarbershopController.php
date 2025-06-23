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
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        $barberBarbershops = $barberBarbershopRepository->findBy(['id_barber' => $this->getUser()->getId()]);
        $barbershop = !empty($barberBarbershops) ? $barberBarbershops[0]->getIdBarbershop() : null;
        if($barbershop) {
            $owner = $barbershop->getCreatedBy();
            if ($owner->getId() !== $this->getUser()->getId()) {
                $owner = null;
            }
        }

        return $this->render('barber/barbershop/dashboard.html.twig', [
            'barbershop' => $barbershop,
            'owner' => $owner ?? null,
        ]);
    }
    #[Route('/view/{id}', name: 'view', methods: ['GET', 'POST'])]
    public function view(Barbershop $barbershop, BarberBarbershopRepository $barberBarbershopRepository, Request $request): Response
    {
        $barberBarbershop = $barberBarbershopRepository->findBy([
            'id_barbershop' => $barbershop->getId()
        ]);

        $barbers = [];
        $seenIds = [];

        foreach ($barberBarbershop as $relation) {
            $barber = $relation->getIdBarber();
            if (!in_array($barber->getId(), $seenIds)) {
                $seenIds[] = $barber->getId();
                $barbers[] = $barber;
            }
        }

        $currentUser = $this->getUser();
        $isParticipant = false;
        foreach ($barbers as $barber) {
            if ($barber->getId() === $currentUser->getId()) {
                $isParticipant = true;
                break;
            }
        }

        if (!$isParticipant) {
            $this->addFlash('danger', 'Você não tem permissão para visualizar essa barbearia.');
            $referer = $request->headers->get('referer');
            return $this->redirect($referer ?? $this->generateUrl('barber_dashboard'));
        }

        $owner = $barbershop->getCreatedBy();
        $isowner = $owner->getId() === $currentUser->getId();

        return $this->render('barber/barbershop/view.html.twig', [
            'barbershop' => $barbershop,
            'barbers' => $barbers,
            'owner' => $owner,
            'isowner' => $isowner,
        ]);
    }

    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function manage(BarbershopRepository $barbershopRepository, Request $request, EntityManagerInterface $em, JoinRequestRepository $joinRequestRepository): Response
    {
        $barbershop = $barbershopRepository->findOneBy(['created_by' => $this->getUser()->getId()]);
        $joinRequests = $joinRequestRepository->findBy([
            'barbershop' => $barbershop,
            'status' => 'pending',
        ]);
        $form = $this->createForm(BarbershopTypeForm::class, $barbershop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
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
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $barbershop = new Barbershop();

        $form = $this->createForm(BarbershopTypeForm::class, $barbershop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $barbershop->setCreatedBy($user);
            $barbershop->setCreatedAt(new \DateTimeImmutable());
            $barbershop->setActive(true);

            $entityManager->persist($barbershop);

            $barberBarbershop = new BarberBarbershop();
            $barberBarbershop->setIdBarber($user);
            $barberBarbershop->setIdBarbershop($barbershop);

            $entityManager->persist($barberBarbershop);

            $entityManager->flush();

            $this->addFlash('success', 'Barbearia criada com sucesso!');

            return $this->redirectToRoute('barber_barbershop_view', ['id' => $barbershop->getId()] );
        }

        return $this->render('barber/barbershop/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/join', name: 'join', methods: ['GET', 'POST'])]
    public function join(BarbershopRepository $barbershopRepository, BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        $user = $this->getUser();
        $barberBarbershops = $barberBarbershopRepository->findBy(['id_barber' => $user->getId()]);
        if (!empty($barberBarbershops)) {
            return $this->redirectToRoute('barber_barbershop_index');
        }
        $barbershops = $barbershopRepository->findAll();
        return $this->render('barber/barbershop/join.html.twig', [
            'barbershops' => $barbershops,
        ]);
    }
    #[Route('/join/{id}', name: 'join_by_id', methods: ['POST'])]
    public function join_by_id(
        int $id,
        BarbershopRepository $barbershopRepository,
        EntityManagerInterface $em
        , Request $request
    ): Response {
        $barbershop = $barbershopRepository->find($id);

        if (!$barbershop) {
            $this->addFlash('danger', 'Barbearia não encontrada.');
            return $this->redirectToRoute('join');
        }

        /** @var User $user */
        $user = $this->getUser();

        // Verifica se já existe pedido pendente
        $existing = $em->getRepository(JoinRequest::class)->findOneBy([
            'user' => $user,
            'barbershop' => $barbershop,
            'status' => 'pending',
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Você já enviou um pedido para essa barbearia.');
            return $this->redirect($request->headers->get('referer'));
        }

        $joinrequest = new JoinRequest();
        $joinrequest->setUser($user);
        $joinrequest->setBarbershop($barbershop);
        $em->persist($joinrequest);
        $em->flush();

        $this->addFlash('success', 'Pedido enviado com sucesso! Aguarde a aprovação.');
        return $this->redirect($request->headers->get('referer'));
    }
    #[Route('/join/rejected/{id}', name: 'join_by_id_rejected', methods: ['POST'])]
    public function join_by_id_rejected(
        int $id,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $joinRequest = $em->getRepository(JoinRequest::class)->find($id);

        if (!$joinRequest) {
            $this->addFlash('danger', 'Pedido não encontrado.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
        }

        $joinRequest->setStatus('rejected');
        $em->persist($joinRequest);
        $em->flush();

        $this->addFlash('success', 'Pedido rejeitado com sucesso.');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
    }

    #[Route('/join/approved/{id}', name: 'join_by_id_approved', methods: ['POST'])]
    public function join_by_id_approved(
        int $id,
        EntityManagerInterface $em,
        Request $request,
    ): Response {
        $joinRequest = $em->getRepository(JoinRequest::class)->find($id);

        if (!$joinRequest) {
            $this->addFlash('danger', 'Pedido não encontrado.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
        }

        $barbershop = $joinRequest->getBarbershop();
        $barber = $joinRequest->getUser();

        $barberBarbershop = new BarberBarbershop();
        $barberBarbershop->setIdBarber($barber);
        $barberBarbershop->setIdBarbershop($barbershop);

        $em->persist($barberBarbershop);
        $em->flush();


        $joinRequest->setStatus('approved');
        $em->persist($joinRequest);
        $em->flush();

        $this->addFlash('success', 'Pedido aceito com sucesso.');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('barber_barbershop_join_by_id'));
    }
    #[Route('/services', name: 'services_index', methods: ['GET'])]
    public function services_index(ServiceRepository $serviceRepository, BarberBarbershopRepository $barberBarbershopRepository): Response
    {
        $barbershop = $barberBarbershopRepository->findBy(['id_barber' => $this->getUser()->getId()])[0]->getIdBarbershop();
        $services = $serviceRepository->findBy(['id_barbershop' => $barbershop, 'active' => true]);
        return $this->render('barber/barbershop/services/index.html.twig', [
            'services' => $services,
            'barbershop' => $barbershop
        ]);
    }
    #[Route('/services/new', name: 'services_new', methods: ['GET', 'POST'])]
    public function services_new(
        Request $request,
        EntityManagerInterface $em,
        BarberBarbershopRepository $barberBarbershopRepository
    ): Response {
        $barbershop = $barberBarbershopRepository->findBy(['id_barber' => $this->getUser()->getId()])[0]->getIdBarbershop();
        $service = new Service();
        $service->setIdBarbershop($barbershop);
        $service->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ServiceTypeForm::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($service);
            $em->flush();

            $this->addFlash('success', 'Serviço criado com sucesso!');

            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        return $this->render('barber/barbershop/services/create.html.twig', [
            'form' => $form,
            'barbershop' => $barbershop,
        ]);
    }
    #[Route('/services/edit/{id}', name: 'services_edit', methods: ['GET', 'POST'])]
    public function services_edit(
        Request $request,
        EntityManagerInterface $entityManager,
        BarbershopRepository $barbershopRepository,
        BarberBarbershopRepository $barberBarbershopRepository,
        ServiceRepository $serviceRepository,
        int $id
    ): Response {
        $barbershop = $barberBarbershopRepository->findBy(['id_barber' => $this->getUser()->getId()])[0]->getIdBarbershop();
        $service = $serviceRepository->find($id);

        if ($service->getIdBarbershop()->getId() !== $barbershop->getId()) {
            throw $this->createNotFoundException('Serviço não encontrado para esta barbearia.');
        }

        $form = $this->createForm(ServiceTypeForm::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Serviço atualizado com sucesso!');
            return $this->redirectToRoute('barber_barbershop_services_index');
        }

        return $this->render('barber/barbershop/services/edit.html.twig', [
            'form' => $form->createView(),
            'barbershop' => $barbershop,
            'service' => $service,
        ]);
    }
    #[Route('/services/{id}/delete', name: 'services_delete', methods: ['POST'])]
    public function services_delete(
        Request $request,
        EntityManagerInterface $entityManager,
        BarbershopRepository $barbershopRepository,
        BarberBarbershopRepository $barberBarbershopRepository,
        ServiceRepository $serviceRepository,
        int $id
    ): Response {
        $barbershop = $barberBarbershopRepository->findBy(['id_barber' => $this->getUser()->getId()])[0]->getIdBarbershop();
        $service = $serviceRepository->find($id);

        if (!$barbershop || !$service) {
            throw $this->createNotFoundException('Barbearia ou serviço não encontrado.');
        }

        if ($service->getIdBarbershop()->getId() !== $barbershop->getId()) {
            throw $this->createAccessDeniedException('Este serviço não pertence à barbearia informada.');
        }

        if ($this->isCsrfTokenValid('delete_service_' . $service->getId(), $request->request->get('_token'))) {
            $service->setActive(false);
            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Serviço removido com sucesso!');
        } else {
            $this->addFlash('danger', 'Token CSRF inválido. Ação cancelada.');
        }

        return $this->redirectToRoute('barber_barbershop_services_index');
    }
}
