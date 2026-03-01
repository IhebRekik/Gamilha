<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class User1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            
  ->add('password', PasswordType::class, [
                'mapped' => true,
                'attr' => [
                    'autocomplete' => 'new-password'
                ]
            ])
            ->add('name')
            ->add('profileImage', FileType::class, [
                'label' => 'Image de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'],
                    ])
                ],
            ])
            ->add('teams', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
