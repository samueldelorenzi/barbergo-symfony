<?php

namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile', name: 'user_profile_')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'view', methods: ['GET'])]
    public function view(): Response
    {
        $user = $this->getUser();
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em) : Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');

            if ($email !== $user->getEmail() && $em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('danger', 'Este email já está em uso.');
                return $this->redirect($request->headers->get('referer'));
            }

            if (!empty($name) && !empty($email)) {
                $user->setName($name);
                $user->setEmail($email);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Perfil atualizado com sucesso!');
                return $this->redirect($request->headers->get('referer'));
            }

            $this->addFlash('error', 'Todos os campos são obrigatórios.');
        }

        return $this->render('user/update.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/change-password', name: 'change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, EntityManagerInterface $em) : Response {
        /** @var User $user */
        $user = $this->getUser();
        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if (!password_verify($currentPassword, $user->getPassword())) {
                $this->addFlash('danger', 'Senha atual incorreta.');
                return $this->redirect($request->headers->get('referer'));
            }

            if ($newPassword === $confirmPassword) {
                $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Senha atualizada com sucesso!');
                return $this->redirect($request->headers->get('referer'));
            }

            $this->addFlash('danger', 'As senhas não coincidem.');
        }

        return $this->render('user/update_password.html.twig', [
            'user' => $user,
        ]);
    }
}
