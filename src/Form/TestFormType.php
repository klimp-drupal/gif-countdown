<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        echo date_default_timezone_get();
        $builder
//          ->add('field_name')
//          ->add('body', TextareaType::class, [
//            'attr' => ['class' => 'tinymce']
//            ]
//          )
//          ->add('DateType', DateType::class)
//          ->add('DateIntervalType', DateIntervalType::class)
          ->add(
            'date',
            DateTimeType::class,
            [
                'data' => new \DateTime("now"),
//                'input' => 'timestamp',
            ])
            ->add('timezone', TimezoneType::class, [
                'data' => date_default_timezone_get(),
//                'data' => new \DateTimeZone(date_default_timezone_get()),
            ])
//          ->add('TimeType', TimeType::class)
//          ->add('BirthdayType', BirthdayType::class)

        ;






    }

//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults([
//        ]);
//    }
}
