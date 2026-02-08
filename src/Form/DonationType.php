<?php

namespace App\Form;

use App\Entity\Donation;
use App\Entity\Stream;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DonationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount')
            ->add('donorName')
            ->add('createdAt')
            ->add('stream', EntityType::class, [
                'class' => Stream::class,
                'choice_label' => 'id',
            ])
            

// ...

    ->add('donorName')
    ->add('amount')
    ->add('stream', EntityType::class, [
        'class' => Stream::class,
        'choice_label' => 'title',
    ]);

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
        ]);
    }
}
