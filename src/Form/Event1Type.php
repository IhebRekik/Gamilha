<?php

namespace App\Form;

use App\Entity\Event;
<<<<<<< HEAD
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
=======
use Symfony\Component\Form\AbstractType;
>>>>>>> origin/Gestion_Vedio_List
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Event1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
<<<<<<< HEAD
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',])
=======
            ->add('date')
>>>>>>> origin/Gestion_Vedio_List
            ->add('game')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
