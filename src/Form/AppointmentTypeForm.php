<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Barbershop;
use App\Entity\User;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class AppointmentTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // exemplo simplificado, os campos id_barber, id_service, appointment_date e appointment_time começam vazios
        $builder
            ->add('barbershop', EntityType::class, [
                'class' => Barbershop::class,
                'choice_label' => fn(Barbershop $b) => $b->getName(),
                'placeholder' => 'Selecione uma barbearia',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'form-select', 'id' => 'barbershop-select'],
            ])
            ->add('id_barber', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'placeholder' => 'Selecione um barbeiro',
                'required' => true,
                'choices' => [],
                'attr' => ['class' => 'form-select', 'id' => 'barber-select'],
            ])
            ->add('id_service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'placeholder' => 'Selecione um serviço',
                'required' => true,
                'choices' => [],
                'attr' => ['class' => 'form-select', 'id' => 'service-select'],
            ])
            ->add('appointment_date', ChoiceType::class, [
                'choices' => [],
                'placeholder' => 'Selecione uma data',
                'required' => true,
                'attr' => ['class' => 'form-select', 'id' => 'date-select'],
            ])
            ->add('appointment_time', ChoiceType::class, [
                'choices' => [],
                'placeholder' => 'Selecione um horário',
                'required' => true,
                'attr' => ['class' => 'form-select', 'id' => 'time-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
