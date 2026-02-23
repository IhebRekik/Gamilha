<?php
// 📁 src/Form/CoachingVideoType.php

namespace App\Form;

use App\Entity\CoachingVideo;
use App\Entity\Playlist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                'label' => 'Titre de la vidéo',
                'attr'  => ['class' => 'form-control', 'placeholder' => 'Ex: Tutoriel débutant...'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr'  => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('videoFile', FileType::class, [
                'label'    => 'Uploader une vidéo (depuis votre PC)',
                'required' => false,
                'mapped'   => false,
                'attr'     => ['class' => 'form-control', 'accept' => 'video/*'],
            ])
            ->add('url', TextType::class, [
                'label'    => 'Ou URL YouTube (embed)',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'placeholder' => 'https://www.youtube.com/embed/XXXX'],
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
            ->add('duration', IntegerType::class, [
                'label'    => 'Durée en secondes (ex: 600 = 10 min)',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'placeholder' => '600', 'min' => 0],
                'help'     => 'Utilisé pour les statistiques de progression de l\'utilisateur.',
            ])
            ->add('premium', CheckboxType::class, [
                'label'    => 'Vidéo premium ?',
                'required' => false,
            ])
            ->add('playlist', EntityType::class, [
                'class'        => Playlist::class,
                'choice_label' => 'title',
                'label'        => 'Playlist associée',
                'attr'         => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CoachingVideo::class,
        ]);
    }
}
