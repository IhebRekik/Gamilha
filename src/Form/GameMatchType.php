<?php

namespace App\Form;

use App\Entity\Bracket;
use App\Entity\Equipe;
use App\Entity\GameMatch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameMatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bracket', EntityType::class, [
                'class' => Bracket::class,
                'choice_label' => fn (Bracket $b) => sprintf('%s - %s', $b->getTypeBracket(), $b->getEvenement()?->getNom() ?? ''),
                'label' => 'Bracket (événement)',
                'placeholder' => 'Choisir un bracket',
            ])
            ->add('dateMatch', DateTimeType::class, ['label' => 'Date du match', 'widget' => 'single_text'])
            ->add('tour', IntegerType::class, ['label' => 'Tour', 'attr' => ['min' => 0]])
            ->add('equipeA', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nomEquipe',
                'label' => 'Équipe A',
                'placeholder' => 'Choisir l\'équipe A',
            ])
            ->add('equipeB', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nomEquipe',
                'label' => 'Équipe B',
                'placeholder' => 'Choisir l\'équipe B',
            ])
            ->add('scoreEquipeA', IntegerType::class, ['label' => 'Score équipe A', 'data' => 0, 'attr' => ['min' => 0]])
            ->add('scoreEquipeB', IntegerType::class, ['label' => 'Score équipe B', 'data' => 0, 'attr' => ['min' => 0]])
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
