<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/cadastro', name: 'app_registration')]
    public function index(Request $request, EntityManagerInterface $emi, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $isBarber = $form->get('isBarber')->getData();
            $user->setRole($isBarber ? 'barber' : 'client');

            $user->setActive(true);
            $emi->persist($user);
            $emi->flush();

            $this->addFlash('success', 'Cadastro realizado com sucesso!');
            return $this->redirectToRoute('app_registration');
        }
        return $this->render('registration/index.html.twig', [
            'form' => $form,
            'controller_name' => 'Cadastro',
        ]);
    }
}
