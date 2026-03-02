<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

// ...

$builder
    ->add('content', CKEditorType::class, [
        'label' => false,
        'config_name' => 'default', // <-- utiliser la config par défaut de fos_ck_editor.yaml
    ])
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Format image non valide'
                    ])
                ]
            ])
             ->add('mediaurl', UrlType::class, [
                'required' => false,
                'label' => false,
                 'help' => 'Lien YouTube, image ou vidéo',

            ]);
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}