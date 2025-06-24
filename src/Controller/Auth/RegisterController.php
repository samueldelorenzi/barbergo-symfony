<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/register', name: 'app_register')]
final class RegisterController extends AbstractController
{
    #[Route('', name: '', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $emi, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setRole($form->get('isBarber')->getData() ? 'barber' : 'client');
            $user->setActive(true);

            $emi->persist($user);
            $emi->flush();

            $this->addFlash('success', 'Cadastro realizado com sucesso!');
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('auth/registration.html.twig', [
            'form' => $form,
        ]);
    }
}
