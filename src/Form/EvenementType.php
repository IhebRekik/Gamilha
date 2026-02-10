<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['label' => 'Nom', 'attr' => ['placeholder' => 'Nom de l\'événement', 'maxlength' => 100]])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('jeu', null, ['label' => 'Jeu', 'attr' => ['maxlength' => 50]])
            ->add('typeEvenement', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [ '' => '' , 'En ligne' => 'online', 'Présentiel' => 'offline' ],
            ])
            ->add('dateDebut', DateType::class, ['label' => 'Date de début', 'widget' => 'single_text'])
            ->add('dateFin', DateType::class, ['label' => 'Date de fin', 'widget' => 'single_text'])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => ['Prévu' => 'prévu', 'En cours' => 'en cours', 'Terminé' => 'terminé'],
            ])
            ->add('regles', TextareaType::class, ['label' => 'Règles', 'required' => false])
            ->add('image', null, ['label' => 'Image (URL ou chemin)', 'required' => false, 'attr' => ['maxlength' => 255]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
