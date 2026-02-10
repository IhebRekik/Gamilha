<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Team1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('members', EntityType::class, [
                'class' => User::class,
<<<<<<< HEAD
                'choice_label' => 'name',
                'multiple' => true,
                'attr' => [
                    'class' => 'select2'
                ],
=======
                'choice_label' => 'id',
                'multiple' => true,
>>>>>>> origin/Gestion_Vedio_List
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
