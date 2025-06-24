<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserFullForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users', name: 'admin_users_')]
final class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $this->userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserFullForm::class, $user, ['is_create' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $role = $form->get('role')->getData();
            $user->setRole($role);
            $user->setActive(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Cadastro realizado com sucesso!');
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('admin/users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $referer = $request->headers->get('referer');

        if ($this->isSelf($user)) {
            $this->addFlash('warning', 'Você não pode editar seu próprio usuário.');
            return $this->redirect($referer);
        }

        $form = $this->createForm(UserFullForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Usuário atualizado com sucesso!');
            return $this->redirect($referer);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        $referer = $request->headers->get('referer');

        if ($this->isSelf($user)) {
            $this->addFlash('warning', 'Você não pode desativar seu próprio usuário.');
            return $this->redirect($referer);
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $user->setActive(false);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Usuário desativado com sucesso!');
        }

        return $this->redirect($referer);
    }

    private function isSelf(User $user): bool
    {
        $userLogged = $this->getUser();
        return $userLogged instanceof User && $userLogged->getId() === $user->getId();
    }
}
