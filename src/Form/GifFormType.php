<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GifFormType extends AbstractType
{
    protected $checkboxes;

    public function __construct()
    {
        $this->checkboxes = [
            'hours',
            'minutes',
            'seconds'
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $checkbox_options = [
//            'required' => FALSE,
//            'disabled' => TRUE,
//        ];
//
//        $first_checkbox = reset($this->checkboxes);
//        foreach ($this->checkboxes as $field_name) {
//            $checkbox_options['label'] = ucwords($field_name);
//
//            // The first checkbox is always enabled.
//            if ($field_name == $first_checkbox) $checkbox_options['disabled'] = FALSE;
//
//            // Add a checkbox.
//            $builder->add($field_name, CheckboxType::class, $checkbox_options);
//
////            $builder->get($field_name)->addEventListener(
////                FormEvents::PRE_SUBMIT,
////                [$this, 'onFieldPreSubmit']
////            );
//
//        }

        $builder->add(
            $builder->create('checkboxes', FormType::class  , array('inherit_data' => true))
//                ->addEventListener(
//                    FormEvents::PRE_SUBMIT,
//                    [$this, 'onPreSubmit']
//                )
//                ->addEventListener(
//                    FormEvents::PRE_SUBMIT,
//                    [$this, 'onCheckboxesPreSetData'],
//                    1
//                )
                ->add('hours', CheckboxType::class, [
                    'required' => FALSE,
                    'label' => 'Hours'
                ])
                ->add('minutes', CheckboxType::class, [
                    'required' => FALSE,
                    'label' => 'Minutes',
                    'disabled' => TRUE,
                ])
                ->add('seconds', CheckboxType::class, [
                    'required' => FALSE,
                    'label' => 'Seconds',
                    'disabled' => TRUE,
                ])
        );


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmitCheckboxesDataPreProcess'],
            0
        );
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmit'],
            -1
        );
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmitDateSet'],
            -2
        );

        // Date selector.
        $builder->add('date', DateTimeType::class, [
            'data' => new \DateTime("now"),
            'with_seconds' => TRUE,
        ]);
//        $builder->add('date', TextType::class, [
//            'data' => 'sdfsdf',
//        ]);

        // Timezone selector.
        $builder->add('timezone', TimezoneType::class, [
            'data' => date_default_timezone_get(),
        ]);

    }

    public function onPreSubmitCheckboxesDataPreProcess(FormEvent $event)
    {
        $data = $event->getData();
        $new_data = [];
        foreach ($this->checkboxes as $checkbox) {
            $new_data[$checkbox] = isset($data['checkboxes'][$checkbox]) ? $data['checkboxes'][$checkbox] : false;
        }
        $data['checkboxes'] = $new_data;
        $event->setData($data);
    }

    public function onFieldPreSubmit(FormEvent $event)
    {
        $el_form = $event->getForm();
        $form = $el_form->getParent();
        $value = $event->getData();
        $name = $el_form->getName();

        $q = 1;

        if ($next_checkbox_field_name = $this->getNextCheckbox($name)) {
            $next_config = $form->get($next_checkbox_field_name)->getConfig();
            $next_options = $next_config->getOptions();
            $next_options['disabled'] = FALSE;

            // Alter all the following checkboxes to be unchecked.
            if (!isset($value) || $value !== 'true') {
                $next_options['disabled'] = TRUE;
            }

            // Add the next checkbox with altered options.
            $form->add($next_checkbox_field_name, get_class($next_config->getType()->getInnerType()), $next_options);
        }
    }

    protected function getNextCheckbox($current)
    {
        foreach ($this->checkboxes as $index => $item) {
            if ($item == $current) {
                return isset($this->checkboxes[$index + 1]) ? $this->checkboxes[$index + 1]: FALSE;
            }
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        // Data contains only checkboxes.
        // If none of them was triggered - data is empty
        // but the form still needs to be processed.
        if (empty($data = $event->getData())) return;

//        $data = $data['checkboxes'];

        $alter_to_false = FALSE;

//        $a = $form->get('checkboxes')->get('hours');

        $checked = '1';
        foreach ($data['checkboxes'] as $checkbox_field_name => &$value) {

//            if (!isset($this->checkboxes[$checkbox_field_name])) continue;

            // If a current checkbox is not checked - we alter all the following checkboxes to be unchecked.
//            if ($value !== 'true') $alter_to_false = TRUE;
            if ($value !== $checked || $alter_to_false) $value = FALSE;

            if ($next_checkbox_field_name = next($this->checkboxes)) {
                $next_config = $form->get('checkboxes')->get($next_checkbox_field_name)->getConfig();
                $next_options = $next_config->getOptions();
                $next_options['disabled'] = FALSE;

                // Alter all the following checkboxes to be unchecked.
                if (!isset($value) || $value !== $checked) {
                    $alter_to_false = TRUE;
                    $next_options['disabled'] = TRUE;
                }

                // Add the next checkbox with altered options.
                $form->get('checkboxes')->add($next_checkbox_field_name, get_class($next_config->getType()->getInnerType()), $next_options);
            }
        }

////         Keep only checked values.
//        $data = $this->filterData($data);
//        $event->setData($data);
//        $event->setData(['checkboxes' => $data]);

//        $parent_form = $form->getParent();
//        $this->setDateElement($parent_form, $data);

//        $date_config = $parent_form->get('date')->getConfig();
//        $date_options = $date_config->getOptions();
//
//        if (!isset($data['seconds']) || !$data['seconds']) $date_options['with_seconds'] = FALSE;
//        if (!isset($data['minutes']) || !$data['minutes']) $date_options['with_minutes'] = FALSE;
//
//        // Date selector.
//        $form->add('date', get_class($date_config->getType()->getInnerType()), $date_options);

//        $form->add('date', TextType::class, [
//            'data' => 'sdfsdf',
//        ]);

//        $data['date'] = '111111';

//        $event->setData($data);

        $a = 1;

    }

    public function onPreSubmitDateSet(FormEvent $event)
    {
        $a = 1;
        $form = $event->getForm();
        $data = $event->getData();

        $date_config = $form->get('date')->getConfig();
        $date_options = $date_config->getOptions();

        if (!isset($data['checkboxes']['seconds']) || !$data['checkboxes']['seconds']) $date_options['with_seconds'] = FALSE;
        if (!isset($data['checkboxes']['minutes']) || !$data['checkboxes']['minutes']) $date_options['with_minutes'] = FALSE;

        // Date selector.
        $form->add('date', get_class($date_config->getType()->getInnerType()), $date_options);
    }

    protected function filterData($data)
    {
        return array_filter($data, function($item) {
            return $item !== 'false';
        });
    }

    protected function setDateElement(&$form, $data)
    {
        $date_config = $form->get('date')->getConfig();
        $date_options = $date_config->getOptions();

        if (!isset($data['seconds']) || !$data['seconds']) $date_options['with_seconds'] = FALSE;
        if (!isset($data['minutes']) || !$data['minutes']) $date_options['with_minutes'] = FALSE;

        // Date selector.
        $form->add('date', get_class($date_config->getType()->getInnerType()), $date_options);
//        $form->add('date', TextType::class, [
//            'data' => 'sdfsdf',
//        ]);
    }

}
