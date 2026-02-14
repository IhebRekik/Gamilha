<?php

namespace App\Form;

use App\Entity\GameMatch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameMatchEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateMatch', DateTimeType::class, [
                'label' => 'Date du match',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['À venir' => 'à venir', 'En cours' => 'en cours', 'Terminé' => 'terminé'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GameMatch::class,
        ]);
    }
}
