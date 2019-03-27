<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TestFormType extends AbstractType
{
    protected $toInclude;

    public function __construct()
    {
        $this->toInclude = [
            'days',
            'hours',
            'minutes',
            'seconds'
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder
//            ->add('date', DateTimeType::class, [
//                'data' => new \DateTime("now"),
//            ])
//            ->add('timezone', TimezoneType::class, [
//                'data' => date_default_timezone_get(),
//            ])
//            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//
//            });
//        ;

//        $to_include = [
//            'days',
//            'hours',
//            'minutes',
//            'seconds'
//        ];

        $to_include = $this->toInclude;

        foreach ($to_include as $field_name) {
            $checkbox_options = [
                'label'    => ucwords($field_name),
                'required' => FALSE,
                'disabled' => TRUE,
//                'disabled' => FALSE,
                'data' => FALSE,
            ];

            // Days is always enabled.
            if ($field_name == 'days') $checkbox_options['disabled'] = FALSE;

            $builder->add($field_name, CheckboxType::class, $checkbox_options);

//            // Handle checkboxes correctly in the form events.
//            // https://stackoverflow.com/questions/46615160/symfony3-dynamic-form-checkbox
//            $builder->get($field_name)->addViewTransformer(new CallbackTransformer(
//                function ($normalized) {
//                    return ($normalized);
//                },
//                function ($submitted) {
//                    return ($submitted === '__false') ? null : $submitted;
//                }
//            ));

            $a = 1;

            if ($next_field_name = next($to_include)) {

//                $builder->get($field_name)->addEventListener(
//                    FormEvents::POST_SUBMIT,
//                    function (FormEvent $event) use ($next_field_name) {
//                        $el_form = $event->getForm();
//                        $parent_form = $el_form->getParent();
//
//                        $a = $el_form->getData();
//                        $b = $el_form->getName();
//
//                        $c = 1;
//
//                        $next_config = $parent_form->get($next_field_name)->getConfig();
//                        $next_options = $next_config->getOptions();
//
//                        if ($el_form->getData()) $next_options['disabled'] = FALSE;
//                        $parent_form->add($next_field_name, get_class($next_config->getType()->getInnerType()), $next_options);
//                    }
//                );

//                $builder->get($field_name)->addEventListener(
//                    FormEvents::POST_SUBMIT,
//                    [$this, 'onFieldPostSubmit']
//                );

            }
        }

        $builder->addEventListener(
//            FormEvents::POST_SUBMIT,
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmit']
        );




//        $builder->add('days', CheckboxType::class, [
//            'label'    => 'Days',
//            'required' => FALSE,
//        ]);

//        $builder->add('days', ChoiceType::class, array(
//            'required' => FALSE,
//            'choices' => array(
//                'Yes' => true,
//                'No' => false,
//            ),
//            'label' => 'Days',
////            'label_attr' => array(
////                'class' => 'middle',
////            ),
//        ));

//        $builder->get('days')
//            ->addViewTransformer(new CallbackTransformer(
//                function ($normalized) {
//                    return ($normalized);
//                },
//                function ($submitted) {
//                    return ($submitted === '__false') ? null : $submitted;
//                }
//            ))
//        ;

//        $options1 = [
//            'label'    => 'Hours',
//            'required' => FALSE,
//            'disabled' => TRUE,
//        ];
//
//        $builder->add('hours', CheckboxType::class, $options1);
//        $builder->add('hours', TextType::class);

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) use ($options) {
//                // this would be your entity, i.e. SportMeetup
//                $data = $event->getData();
////
////                $a = $days_form->getData();
////                if ($days_form->getData()) $options['disabled'] = FALSE;
////                $days_form->add('hours', CheckboxType::class, $options);
////
//////                $formModifier($event->getForm(), $data->getSport());
//
//                $form = $event->getForm();
//
//                $a = $form->get('days')->getData();
//
//                // Get configuration & options of specific field
//                $config = $form->get('hours')->getConfig();
//                $hours_options = $config->getOptions();
//
//                $form->add(
//                // Replace original field...
//                    'hours',
////                    $config->getType()->getName(),
////                    CheckboxType::class,
//                    get_class($config->getType()->getInnerType()),
//                    // while keeping the original options...
//                    array_replace(
//                        $hours_options,
//                        [
//                            // replacing specific ones
//                            'disabled' => true,
//                        ]
//                    )
//                );
//
//            }
//        );

//        $builder->get('days')->addEventListener(
//            FormEvents::POST_SUBMIT,
//            function (FormEvent $event)
//            {
//                $days_form = $event->getForm();
//
//                // Get configuration & options of specific field
//                $config = $days_form->getParent()->get('hours')->getConfig();
//                $hours_options = $config->getOptions();
//
//                if ($days_form->getData()) $hours_options['disabled'] = FALSE;
//                $days_form->getParent()->add('hours', CheckboxType::class, $hours_options);
//            }
//        );

        $builder->add('date', DateTimeType::class, [
            'data' => new \DateTime("now")
        ]);
        $builder->add('timezone', TimezoneType::class, [
            'data' => date_default_timezone_get(),
        ]);
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $form = $event->getForm();
//            $a = FALSE;
//            if ($a) $form->add('name', TextType::class);
//        });

    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $alter_to_false = FALSE;

        foreach ($data as $checkbox_field_name => $value) {

            if ($value !== 'true') $alter_to_false = TRUE;
            if ($alter_to_false) $value = FALSE;

            if ($next_checkbox_field_name = next($this->toInclude)) {
                $next_config = $form->get($next_checkbox_field_name)->getConfig();
                $next_options = $next_config->getOptions();
                $next_options['disabled'] = FALSE;

                if (!isset($value) || $value !== 'true') {
                    $alter_to_false = TRUE;
                    $next_options['disabled'] = TRUE;
                }

                $form->add($next_checkbox_field_name, get_class($next_config->getType()->getInnerType()), $next_options);
            }
        }

        foreach ($data as $key => $item) {
            if($item !== 'true') unset($data[$key]);
        }
        $event->setData($data);

    }

    public function onFieldPostSubmit(FormEvent $event)
    {
        $el_form = $event->getForm();
        $parent_form = $el_form->getParent();

        $value = $el_form->getData();
        $checkbox_field_name = $el_form->getName();

        $current = reset($this->toInclude);
        while (current($this->toInclude) !== $checkbox_field_name) {
            $current = next($this->toInclude);
        }
        $next_checkbox_field_name = next($this->toInclude);

        $next_config = $parent_form->get($next_checkbox_field_name)->getConfig();
        $next_options = $next_config->getOptions();

//        $next_options['disabled'] = FALSE;
        $next_options['disabled'] = $value ? FALSE : TRUE;
        $next_options['value'] = FALSE;

        $parent_form->add($next_checkbox_field_name, get_class($next_config->getType()->getInnerType()), $next_options);

        $parent_form->add('name', TextType::class);

        $c = 1;


    }

}
