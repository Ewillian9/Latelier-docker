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
                    'placeholder' => 'email',
                    'maxlength' => 64
                ],
                'constraints' => [
                    new Length([
                        'max' => 64,
                        'maxMessage' => 'form.maxLength',
                    ])
                ]
            ])
            ->add('username', TextType::class, [
                'attr' => [
                    'placeholder' => 'username',
                    'minlength' => 4,
                    'maxlength' => 32
                ],
                'constraints' => [
                    new Length([
                        'min' => 4,
                        'minMessage' => 'form.minLength',
                        'max' => 32,
                        'maxMessage' => 'form.maxLength'
                    ])
                ]
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => 'form.profile.bio',
                    'maxlength' => 1000
                ],
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'form.maxlength'
                    ])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => false,
                'first_options' => [
                    'attr' => [
                        'placeholder' => 'form.profile.password.new',
                        'autocomplete' => 'new-password',
                    ],
                    'constraints' => [
                        new Length([
                            'min' => 8,
                            'minMessage' => 'form.minLength',
                            'max' => 128,
                            'maxMessage' => 'form.maxLength',
                        ]),
                    ],
                ],
                'second_options' => [
                    'attr' => [
                        'placeholder' => 'form.profile.password.confirm',
                        'autocomplete' => 'new-password'
                    ],
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
