<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Form\AppointmentTypeForm;
use App\Repository\BarbershopRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class AppointmentController extends AbstractController
{
    #[Route('/appointment', name: 'app_appointment')]
    public function new(Request $request, EntityManagerInterface $em, BarbershopRepository $barbershopRepository): Response
    {
        $appointment = new Appointment();
        $barbershops = $barbershopRepository->findAllBarbershops();

        // Passe a entidade $appointment para o form
        $form = $this->createForm(AppointmentTypeForm::class, $appointment, [
            'barbershops' => $barbershops
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pegue a data e hora do request (porque não tem no form)
            $selectedDate = $request->request->get('date');
            $selectedTime = $request->request->get('time');

            if (!$selectedDate || !$selectedTime) {
                $this->addFlash('error', 'Data e horário são obrigatórios.');
                // Re-exibe o form com erro
                return $this->render('appointment/index.html.twig', [
                    'barbershops' => $barbershops,
                    'form' => $form
                ]);
            }

            $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i', $selectedDate . ' ' . $selectedTime);
            if (!$appointmentDateTime) {
                $this->addFlash('error', 'Data e hora inválidas.');
                return $this->render('appointment/index.html.twig', [
                    'barbershops' => $barbershops,
                    'form' => $form->createView(),
                ]);
            }
            $appointment->setAppointmentDatetime($appointmentDateTime);

            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'Você precisa estar logado para agendar.');
                return $this->redirectToRoute('login_route_name'); // ajuste para sua rota de login
            }
            $appointment->setIdClient(id_client: $user);
            // Defina status padrão
            $appointment->setStatus('scheduled');

            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', 'Agendamento realizado com sucesso!');
            return $this->redirectToRoute('appointments_list');
        }

        return $this->render('appointment/index.html.twig', [
            'barbershops' => $barbershops,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/appointment/profile', name: 'app_client_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->render('appointment/client/profile.html.twig', [
            'controller_name' => 'AppointmentController',
            'user' => $user,
        ]);
    }

    #[Route('/appointment/my_appointments', name: 'app_my_appointments')]
    public function my_appointments(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $appointments = $em->getRepository(Appointment::class)->findBy([
            'id_client' => $user
        ], ['appointment_datetime' => 'DESC']);

        return $this->render('appointment/client/appointments.html.twig', [
            'appointments' => $appointments,
            'user' => $user
        ]);
    }

    #[Route('/appointment/profile/update_password', name: 'app_client_update_password')]
    public function change_password(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if (!password_verify($currentPassword, $user->getPassword())) {
                $this->addFlash('danger', 'Senha atual incorreta.');
                return $this->redirectToRoute('app_client_update_password');
            }

            if ($newPassword === $confirmPassword) {
                $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Senha atualizada com sucesso!');
                return $this->redirectToRoute('app_client_update_password');
            }

            $this->addFlash('danger', 'As senhas não coincidem.');
        }

        return $this->render('appointment/client/password_update.html.twig', [
            'controller_name' => 'AppointmentController',
            'user' => $user,
        ]);
    }

    #[Route('/appointment/profile/update', name: 'app_client_update_profile')]
    public function update(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');

            if ($email !== $user->getEmail() && $em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('danger', 'Este email já está em uso.');
                return $this->redirectToRoute('app_client_update_profile');
            }

            if (!empty($name) && !empty($email)) {
                $user->setName($name);
                $user->setEmail($email);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Perfil atualizado com sucesso!');
                return $this->redirectToRoute('app_client_update_profile');
            }

            $this->addFlash('error', 'Todos os campos são obrigatórios.');
        }

        return $this->render('appointment/client/profile_update.html.twig', [
            'user' => $user,
        ]);
    }
}
