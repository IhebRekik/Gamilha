<?php

namespace App\Form;

use App\Entity\Abonnement;
use App\Entity\user;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbonnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('prix')
            ->add('duree')
            ->add('options', ChoiceType::class, [
                'choices' => Abonnement::FEATURES,
                'expanded' => true,   // ✅ checkboxes
                'multiple' => true,   // ✅ plusieurs choix
                'label' => 'Fonctionnalités accessibles'
            ])
            ->add('avantages', TextareaType::class, [
                'label' => 'Avantages (un par ligne)',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                    'placeholder' => "Tous les avantages Gratuit\nAccès illimité aux playlists de coaching\nCoach IA avancé\nStatistiques détaillées\nStreaming jusqu'à 1080p\n...",
                ],
                'mapped' => false, // On va le gérer manuellement dans le contrôleur
                'help' => 'Chaque ligne correspond à un avantage affiché avec une coche ✓',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Abonnement::class,
        ]);
    }
}
