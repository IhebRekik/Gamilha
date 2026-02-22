<?php

namespace App\Form;

use App\Entity\CoachingVideo;
use App\Entity\Playlist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoachingVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la vidéo'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Uploader une vidéo (PC)',
                'required' => false,
                'mapped' => false
            ])
            ->add('url', TextType::class, [
                'label' => 'Ou URL de la vidéo (YouTube, etc.)',
                'required' => false
            ])
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => [
                    'Débutant' => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé' => 'avance',
                ]
            ])
            ->add('premium', CheckboxType::class, [
                'label' => 'Vidéo premium ?',
                'required' => false
            ])
            ->add('playlist', EntityType::class, [
                'class' => Playlist::class,
                'choice_label' => 'title',
                'label' => 'Playlist associée'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CoachingVideo::class,
        ]);
    }
}
