<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersFullForm;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/users')]
final class UsersController extends AbstractController
{
    #[Route(name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $usersRepository): Response
    {
        return $this->render('users/index.html.twig', [
            'users' => $usersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $emi, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersFullForm::class, $user, ['is_create' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $role = $form->get('role')->getData();
            $user->setRole($role);

            $user->setActive(true);
            $emi->persist($user);
            $emi->flush();

            $this->addFlash('success', 'Cadastro realizado com sucesso!');
            return $this->redirectToRoute('app_users_index');
        }

        return $this->render('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->isSelf($user)) {
            $this->addFlash('warning', 'Você não pode editar seu próprio usuário.');
            return $this->redirectToRoute('app_users_index');
        }

        $form = $this->createForm(UsersFullForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Usuário atualizado com sucesso!');
            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isSelf($user)) {
            $this->addFlash('warning', 'Você não pode desativar seu próprio usuário.');
            return $this->redirectToRoute('app_users_index');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $userRepository = $entityManager->getRepository(Users::class);
            $user = $userRepository->find($user->getId());
            if (!$user) {
                throw $this->createNotFoundException('User not found');
            }
            $user->setActive(false);
            $user->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Usuário desativado com sucesso!');
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }
    private function isSelf(Users $user): bool
    {
        $userLogged = $this->getUser();
        return $userLogged instanceof Users && $userLogged->getId() === $user->getId();
    }
}
