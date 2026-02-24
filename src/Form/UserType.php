<?php

namespace App\Form;

use App\Entity\Abonnement;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom complet',
                'attr' => [
                    'placeholder' => 'Entrez le nom complet'
                ]
            ])
            ->add('email', null, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'exemple@domaine.com'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Laisser vide pour conserver l\'actuel'
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Modérateur' => 'ROLE_MODERATOR',
                    'Coach' => 'ROLE_COACH',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Compte actif',
                'required' => false,
            ])
            ->add('banUntil', DateTimeType::class, [
                'label' => 'Banni jusqu\'au',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('reports', IntegerType::class, [
                'label' => 'Nombre de signalements',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'value' => 0
                ]
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Image de profil (PNG, JPG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (PNG/JPEG).',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
