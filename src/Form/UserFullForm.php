<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFullForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isCreate = $options['is_create'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nome',
                'required' => true,
                'constraints' => $isCreate ? [
                    new NotBlank(['message' => 'Por favor, insira seu nome']),
                ] : [],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'required' => true,
                'constraints' => $isCreate ? [
                    new NotBlank(['message' => 'Por favor, insira seu e-mail']),
                    new Email(['message' => 'E-mail inválido']),
                ] : [],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Senha',
                'required' => $isCreate,
                'constraints' => $isCreate ? [
                    new NotBlank(['message' => 'Por favor, insira uma senha']),
                ] : [],
                'attr' => $isCreate ? [] : [
                    'placeholder' => 'Deixe em branco para manter a senha atual',
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Função',
                'required' => true,
                'constraints' => $isCreate ? [
                    new NotBlank(['message' => 'Por favor, insira a função']),
                ] : [],
                'choices' => [
                    'Cliente' => 'client',
                    'Barbeiro' => 'barber',
                    'Administrador' => 'admin',
                ],
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Ativo',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_create' => false,
        ]);
    }
}
