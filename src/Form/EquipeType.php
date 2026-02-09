<?php

namespace App\Form;

use App\Entity\Equipe;
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
            ->add('nomEquipe', null, ['label' => 'Nom de l\'équipe', 'attr' => ['maxlength' => 100]])
            ->add('tag', null, ['label' => 'Tag', 'required' => false, 'attr' => ['maxlength' => 10]])
            ->add('logo', null, ['label' => 'Logo (URL)', 'required' => false])
            ->add('pays', null, ['label' => 'Pays', 'required' => false, 'attr' => ['maxlength' => 50]])
            ->add('dateCreation', DateType::class, ['label' => 'Date de création', 'widget' => 'single_text', 'required' => false])
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => ['Amateur' => 'amateur', 'Semi-pro' => 'semi-pro', 'Pro' => 'pro'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}
