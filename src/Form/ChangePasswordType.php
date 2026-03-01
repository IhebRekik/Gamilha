<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control bg-dark text-white border-secondary',
                    'placeholder' => '••••••••',
                    'autocomplete' => 'current-password'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre mot de passe actuel'])
                ]
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control bg-dark text-white border-secondary',
                        'placeholder' => '••••••••',
                        'autocomplete' => 'new-password'
                    ],
                    'constraints' => [
                        new NotBlank(['message' => 'Le nouveau mot de passe est obligatoire']),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
                            'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre'
                        ])
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmer le nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control bg-dark text-white border-secondary',
                        'placeholder' => '••••••••',
                        'autocomplete' => 'new-password'
                    ]
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
