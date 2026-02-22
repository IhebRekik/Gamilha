<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class FrontEvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['label' => 'Nom', 'attr' => ['placeholder' => 'Nom de l\'événement']])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('jeu', null, ['label' => 'Jeu'])
            ->add('typeEvenement', ChoiceType::class, [
                'label' => 'Type',
                'choices' => ['En ligne' => 'online', 'Présentiel' => 'offline'],
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['Prévu' => 'prévu', 'En cours' => 'en cours', 'Terminé' => 'terminé'],
            ])
            ->add('regles', TextareaType::class, ['label' => 'Règles', 'required' => false])
            ->add('image', null, ['label' => 'Image (URL)', 'required' => false])
            ->add('equipesParticipantes', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'nomEquipe',
                'label' => 'Équipes participantes',
                'multiple' => true,
                'expanded' => true,
                'constraints' => [new Count(min: 2, minMessage: 'Sélectionnez au moins 2 équipes.')],
            ])
            ->add('typeBracket', ChoiceType::class, [
                'label' => 'Type de bracket',
                'mapped' => false,
                'choices' => [
                    'Single elimination' => 'single elimination',
                    'Double elimination' => 'double elimination',
                ],
                'data' => 'single elimination',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
