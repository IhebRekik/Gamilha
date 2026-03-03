<?php
// 📁 src/Form/PlaylistType.php

namespace App\Form;

use App\Entity\Playlist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class PlaylistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la playlist',
                'attr'  => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr'  => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('niveau', ChoiceType::class, [
                'label'   => 'Niveau',
                'choices' => [
                    'Débutant'      => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé'        => 'avance',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('categorie', ChoiceType::class, [
                'label'   => 'Catégorie',
                'choices' => [
                    'Action'   => 'action',
                    'Aventure' => 'aventure',
                    'Sport'    => 'sport',
                    'Course'   => 'course',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('imageFile', FileType::class, [
                'label'       => 'Image de la playlist',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new Image([
                        'maxSize'          => '5M',
                        'mimeTypes'        => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WEBP)',
                        'maxSizeMessage'   => 'L\'image ne doit pas dépasser 5Mo',
                    ])
                ],
                'attr' => ['accept' => 'image/*', 'class' => 'form-control'],
                'help' => 'Formats acceptés : JPEG, PNG, GIF, WEBP (max 5Mo)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'     => Playlist::class,
            'csrf_protection'=> true,
        ]);
    }
}
