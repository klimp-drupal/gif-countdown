<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
        foreach ($this->toInclude as $field_name) {
            $checkbox_options = [
                'label'    => ucwords($field_name),
                'required' => FALSE,
                'disabled' => TRUE,
                'data' => FALSE,
            ];

            // Days is always enabled.
            if ($field_name == 'days') $checkbox_options['disabled'] = FALSE;

            // Add a checkbox.
            $builder->add($field_name, CheckboxType::class, $checkbox_options);
        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmit']
        );

        // Date selector.
        $builder->add('date', DateTimeType::class, [
            'data' => new \DateTime("now")
        ]);

        // Timezone selector.
        $builder->add('timezone', TimezoneType::class, [
            'data' => date_default_timezone_get(),
        ]);

    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $alter_to_false = FALSE;

        foreach ($data as $checkbox_field_name => &$value) {

            // If a current checkbox is not checked - we alter all the following checkboxes to be unchecked.
            if ($value !== 'true') $alter_to_false = TRUE;
            if ($alter_to_false) $value = FALSE;

            if ($next_checkbox_field_name = next($this->toInclude)) {
                $next_config = $form->get($next_checkbox_field_name)->getConfig();
                $next_options = $next_config->getOptions();
                $next_options['disabled'] = FALSE;

                // Alter all the following checkboxes to be unchecked.
                if (!isset($value) || $value !== 'true') {
                    $alter_to_false = TRUE;
                    $next_options['disabled'] = TRUE;
                }

                // Add a checkbox with altered options.
                $form->add($next_checkbox_field_name, get_class($next_config->getType()->getInnerType()), $next_options);
            }
        }

        // Keep only checked values.
        $data = array_filter($data, function($item) {
            return $item == 'true';
        });
        $event->setData($data);

    }

}
