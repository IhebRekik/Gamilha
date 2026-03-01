<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'class' => 'form-control bg-dark text-white border-secondary',
                    'placeholder' => 'Votre nom'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'class' => 'form-control bg-dark text-white border-secondary',
                    'placeholder' => 'votre@email.com'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez saisir une adresse email valide'])
                ]
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control bg-dark text-white border-secondary',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF, WEBP)',
                    ])
                ],
                'help' => 'Formats acceptés: JPEG, PNG, GIF, WEBP (max 2Mo)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
