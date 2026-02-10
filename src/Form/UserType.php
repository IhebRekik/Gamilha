<?php

namespace App\Form;

<<<<<<< HEAD
use App\Entity\Abonnement;
=======
>>>>>>> origin/Gestion_Vedio_List
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
<<<<<<< HEAD
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
=======
>>>>>>> origin/Gestion_Vedio_List

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
<<<<<<< HEAD
            ->add('name')
            ->add('email')
            ->add('password', PasswordType::class, [
                'mapped' => true,
                'attr' => [
                    'autocomplete' => 'new-password'
                ]
            ]);

=======
            ->add('email')
           
            ->add('password')
            ->add('name')
           
        ;
        
>>>>>>> origin/Gestion_Vedio_List
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
<<<<<<< HEAD
=======
    
>>>>>>> origin/Gestion_Vedio_List
}
