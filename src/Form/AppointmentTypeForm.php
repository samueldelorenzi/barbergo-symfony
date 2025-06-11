<?php

namespace App\Form;

use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('barbershop', ChoiceType::class, [
                'label' => 'Barbearia',
                'mapped' => false,
                'choices' => $options['barbershops'],
                'choice_label' => fn($barbershop) => $barbershop->getName() . ' - ' . $barbershop->getAddress(),
                'choice_value' => 'id',
                'placeholder' => 'Selecione uma barbearia',
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'barbershop-select'
                ],
            ])
            ->add('barber', ChoiceType::class, [
                'mapped' => false,
                'choices' => [],
                'placeholder' => 'Selecione um barbeiro',
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'barber-select'
                ],
            ])
            ->add('service', ChoiceType::class, [
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
            'barbershops' => [],
        ]);
    }
}
