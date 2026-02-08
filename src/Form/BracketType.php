<?php

namespace App\Form;

use App\Entity\Bracket;
use App\Entity\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BracketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeBracket', ChoiceType::class, [
                'label' => 'Type de bracket',
                'choices' => [
                    'Single elimination' => 'single elimination',
                    'Double elimination' => 'double elimination',
                ],
            ])
            ->add('nombreTours', IntegerType::class, ['label' => 'Nombre de tours', 'attr' => ['min' => 0]])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['En attente' => 'en attente', 'En cours' => 'en cours', 'Terminé' => 'terminé'],
            ])
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => 'nom',
                'label' => 'Événement',
                'placeholder' => 'Choisir un événement',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bracket::class,
        ]);
    }
}
