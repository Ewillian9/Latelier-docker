<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Email',
                    'maxlength' => 64
                ],
                'constraints' => [
                    new Length([
                        'max' => 64,
                        'maxMessage' => 'Cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('username', TextType::class, [
                'attr' => [
                    'placeholder' => 'Username',
                    'maxlength' => 32
                ],
                'constraints' => [
                    new Length([
                        'max' => 32,
                        'maxMessage' => 'Cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => 'Write your bio here, explain your work, passion, inspiration and what brings you',
                    'maxlength' => 1024
                ],
                'constraints' => [
                    new Length([
                        'max' => 1024,
                        'maxMessage' => 'Cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'first_options' => [
                    'label' => 'New password',
                    'attr' => [
                        'placeholder' => 'New password (128 max)',
                        'autocomplete' => 'new-password'],
                    'constraints' => [
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 128,
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Repeat password',
                    'attr' => [
                        'placeholder' => 'Repeat password',
                        'autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'The password fields must match.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
