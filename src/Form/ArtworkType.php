<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Form\ArtworkImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;

class ArtworkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('images', CollectionType::class, [
                'entry_type' => ArtworkImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
            ])
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'form.artwork.title',
                    'maxlength' => 32,
                ],
                'constraints' => [
                    new Length([
                        'max' => 32,
                        'maxMessage' => 'form.artwork.title.maxLength',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                    'placeholder' => 'form.artwork.description',
                    'maxlength' => 4000,
                ],
                'constraints' => [
                    new Length([
                        'max' => 4000,
                        'maxMessage' => 'form.artwork.description.maxLength',
                    ]),
                ],
            ])
            ->add('keywords', TextType::class, [
                'attr' => [
                    'placeholder' => 'form.artwork.keywords',
                    'maxlength' => 16,
                ],
                'constraints' => [
                    new Length([
                        'max' => 16,
                        'maxMessage' => 'form.artwork.keywords.maxLength',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artwork::class,
        ]);
    }
}
