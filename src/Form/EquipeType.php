<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEquipe', null, ['label' => 'Nom de l\'équipe'])
            ->add('tag', null, ['label' => 'Tag', 'required' => false])
            ->add('logo', null, ['label' => 'Logo (URL)', 'required' => false])
            ->add('pays', null, ['label' => 'Pays', 'required' => false])
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de création', 
                'widget' => 'single_text', 
                'required' => false,
                'empty_data' => null,
            ])
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => ['Amateur' => 'amateur', 'Semi-pro' => 'semi-pro', 'Pro' => 'pro'],
            ])
        ;

        // Ajouter le champ membres seulement si l'option 'include_members' est true
        if ($options['include_members'] ?? false) {
            $builder->add('members', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'label' => 'Membres',
                'attr' => ['class' => 'form-select'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
            'include_members' => false,
        ]);
    }
}
