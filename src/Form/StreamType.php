<?php

namespace App\Form;

use App\Entity\Stream;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class StreamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('game', ChoiceType::class, [
                'choices' => [
                    'CS2' => 'CS2',
                    'Valorant' => 'Valorant',
                    'League of Legends' => 'LoL',
                    'Dota 2' => 'Dota2',
                ],
                'placeholder' => 'Choisir un jeu'
            ])
            ->add('viewers', IntegerType::class, [
                'label' => 'Viewers',
                'required' => false,
            ])
           
            ->add('url', TextType::class, [
                'label' => 'URL du stream',
                'required' => false,
            ])
            ->add('thumbnail', TextType::class, [
                'required' => false,
                'label' => 'Image (URL)',
                'attr' => [
                    'placeholder' => 'https://example.com/image.jpg'
                ]
            ]);
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stream::class,
        ]);
    }
}
