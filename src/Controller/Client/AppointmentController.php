<?php

namespace App\Controller\Client;

use App\Entity\Appointment;
use App\Entity\BarberBarbershop;
use App\Entity\Service;
use App\Entity\User;
use App\Form\AppointmentTypeForm;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client/appointment', name: 'client_appointment_')]
final class AppointmentController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(AppointmentTypeForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $request->request->all();
            $formData = $data['appointment_type_form'] ?? [];

            if (
                empty($formData['appointment_time']) ||
                empty($formData['appointment_date']) ||
                empty($formData['barbershop']) ||
                empty($formData['id_service']) ||
                empty($formData['id_barber'])
            ) {
                $this->addFlash('danger', 'Campos vazios.');
                return $this->redirect($request->headers->get('referer'));
            }

            $barber = $this->em->getRepository(User::class)->find($formData['id_barber']);
            $service = $this->em->getRepository(Service::class)->find($formData['id_service']);
            $client = $this->getUser();

            if (!$barber || !$service || !$client instanceof User) {
                $this->addFlash('danger', 'Barbeiro, serviço ou cliente inválido.');
                return $this->redirect($request->headers->get('referer'));
            }

            $appointment = new Appointment();
            $appointment->setIdClient($client);
            $appointment->setIdBarber($barber);
            $appointment->setIdService($service);
            $appointment->setAppointmentDate(new DateTime($formData['appointment_date']));
            $appointment->setAppointmentTime(DateTime::createFromFormat('H:i', $formData['appointment_time']));
            $appointment->setStatus('Agendado');

            $this->em->persist($appointment);
            $this->em->flush();

            $this->addFlash('success', 'Agendamento realizado com sucesso!');
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('client/appointment/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function my_appointments(): Response
    {
        $user = $this->getUser();

        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from(Appointment::class, 'a')
            ->where('a.id_client = :client')
            ->setParameter('client', $user)
            ->orderBy('a.appointment_date', 'ASC')
            ->addOrderBy('a.appointment_time', 'ASC');

        $appointments = $qb->getQuery()->getResult();

        $appointmentsBarbershops = [];

        foreach ($appointments as $appointment) {
            $barbershopsNames = [];

            $barber = $appointment->getIdBarber();

            if ($barber) {
                $barberBarbershops = $this->em->getRepository(BarberBarbershop::class)
                    ->findBy(['id_barber' => $barber]);

                foreach ($barberBarbershops as $barberBarbershop) {
                    $barbershop = $barberBarbershop->getIdBarbershop();
                    if ($barbershop) {
                        $barbershopsNames[] = $barbershop->getName();
                    }
                }
            }

            $appointmentsBarbershops[$appointment->getId()] = $barbershopsNames;
        }

        return $this->render('client/appointment/list.html.twig', [
            'appointments' => $appointments,
            'user' => $user,
            'appointmentsBarbershops' => $appointmentsBarbershops,
        ]);
    }
}
