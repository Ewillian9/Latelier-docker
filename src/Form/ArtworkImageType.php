<?php

namespace App\Form;

use App\Entity\ArtworkImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
                    'maxSize' => '8M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/jpg',
                        'image/avif',
                    ],
                    'mimeTypesMessage' => 'Seuls les fichiers WEBP, AVIF, JPG ou PNG  sont autorisés.',
                    'maxSizeMessage' => 'L’image ne doit pas dépasser 8 Mo.',
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
