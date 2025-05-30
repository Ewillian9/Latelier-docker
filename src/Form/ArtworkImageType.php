<?php

namespace App\Form;

use App\Entity\ArtworkImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArtworkImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('imageFile', VichImageType::class, [
            'required' => false,
            'label' => false,
            'download_uri' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/avif',
                    ],
                    'mimeTypesMessage' => 'Only WEBP, AVIF, JPEG or PNG images are allowed',
                    'maxSizeMessage' => 'Image must be 5 Mo max',
                ])
            ],
        ])
        ->add('legend', TextType::class, [
            'label' => false,
            'required' => false,
            'attr' => [
                'placeholder' => 'Image caption...',
                'maxlength' => 100,
            ],
            'constraints' => [
                new Length([
                    'max' => 100,
                    'maxMessage' => 'Legend cannot be longer than {{ limit }} characters',
                ])
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArtworkImage::class,
        ]);
    }
}
