<?php

namespace App\Controller\Barber;

use App\Entity\JoinRequest;
use App\Entity\User;
use App\Repository\BarberBarbershopRepository;
use App\Repository\BarbershopRepository;
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
    public function join(BarbershopRepository $barbershopRepository): Response
    {
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
}
