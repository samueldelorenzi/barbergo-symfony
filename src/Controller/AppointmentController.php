<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\BarberBarbershop;
use App\Entity\User;
use App\Entity\Service;
use App\Form\AppointmentTypeForm;
use App\Repository\BarberBarbershopRepository;
use App\Repository\BarbershopRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/appointment')]
final class AppointmentController extends AbstractController
{
    #[Route('', name: 'appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AppointmentTypeForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $request->request->all();
            $formData = $data['appointment_type_form'];

            if (
                !$formData['appointment_time'] ||
                !$formData['appointment_date'] ||
                !$formData['barbershop'] ||
                !$formData['id_service'] ||
                !$formData['id_barber']
            ) {
                $this->addFlash('danger', 'Campos vazios.');
                return $this->redirectToRoute('appointment_new');
            }

            $barber = $em->getRepository(User::class)->find($formData['id_barber']);
            $service = $em->getRepository(Service::class)->find($formData['id_service']);

            $client = $this->getUser();

            if (!$barber || !$service || !$client instanceof User) {
                $this->addFlash('danger', 'Barbeiro, serviço ou cliente inválido.');
                return $this->redirectToRoute('appointment_new');
            }

            $appointment = new Appointment();
            $appointment->setIdClient($client);
            $appointment->setIdBarber($barber);
            $appointment->setIdService($service);
            $appointment->setAppointmentDate(new DateTime($formData['appointment_date']));
            $appointment->setAppointmentTime(DateTime::createFromFormat('H:i', $formData['appointment_time']));
            $appointment->setStatus('Agendado');

            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', 'Agendamento realizado com sucesso!');
            return $this->redirectToRoute('appointment_new');
        }

        return $this->render('appointment/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/profile', name: 'appointment_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->render('appointment/client/profile.html.twig', [
            'controller_name' => 'AppointmentController',
            'user' => $user,
        ]);
    }

    #[Route('/my_appointments', name: 'appointment_my_appointments', methods: ['GET'])]
    public function my_appointments(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $qb = $em->createQueryBuilder();
        $qb->select('a')
            ->from(Appointment::class, 'a')
            ->where('a.id_client = :client')
            ->setParameter('client', $user)
            ->orderBy('a.appointment_date', 'ASC')
            ->addOrderBy('a.appointment_time', 'ASC');

        $appointments = $qb->getQuery()->getResult();

        // Array para guardar as barbearias relacionadas a cada agendamento
        $appointmentsBarbershops = [];

        foreach ($appointments as $appointment) {
            $barbershopsNames = [];

            $barber = $appointment->getIdBarber();

            if ($barber) {
                // Buscar todas as relações BarberBarbershop desse barbeiro
                $barberBarbershops = $em->getRepository(BarberBarbershop::class)
                    ->findBy(['id_barber' => $barber]);

                foreach ($barberBarbershops as $barberBarbershop) {
                    $barbershop = $barberBarbershop->getIdBarbershop();
                    if ($barbershop) {
                        $barbershopsNames[] = $barbershop->getName();
                    }
                }
            }

            // Guardar array com nomes das barbearias para o agendamento
            $appointmentsBarbershops[$appointment->getId()] = $barbershopsNames;
        }

        return $this->render('appointment/client/appointments.html.twig', [
            'appointments' => $appointments,
            'user' => $user,
            'appointmentsBarbershops' => $appointmentsBarbershops,
        ]);
    }

    #[Route('/profile/update_password', name: 'appointment_update_password', methods: ['GET', 'POST'])]
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
                return $this->redirectToRoute('appointment_update_password');
            }

            if ($newPassword === $confirmPassword) {
                $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Senha atualizada com sucesso!');
                return $this->redirectToRoute('appointment_update_password');
            }

            $this->addFlash('danger', 'As senhas não coincidem.');
        }

        return $this->render('appointment/client/password_update.html.twig', [
            'controller_name' => 'AppointmentController',
            'user' => $user,
        ]);
    }

    #[Route('/profile/update', name: 'appointment_update_profile', methods: ['GET', 'POST'])]
    public function update(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');

            if ($email !== $user->getEmail() && $em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('danger', 'Este email já está em uso.');
                return $this->redirectToRoute('appointment_update_profile');
            }

            if (!empty($name) && !empty($email)) {
                $user->setName($name);
                $user->setEmail($email);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Perfil atualizado com sucesso!');
                return $this->redirectToRoute('appointment_update_profile');
            }

            $this->addFlash('error', 'Todos os campos são obrigatórios.');
        }

        return $this->render('appointment/client/profile_update.html.twig', [
            'user' => $user,
        ]);
    }
}
