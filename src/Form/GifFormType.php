<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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

        // Checkboxes.
        $builder->add(
            $builder->create('checkboxes', FormType::class  , array('inherit_data' => true))
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

        // Preprocess the checkboxes data.
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmitCheckboxesDataPreProcess'],
            0
        );

        // Update the checkboxes.
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmitUpdateCheckboxes'],
            -1
        );

        // Alter the Date widget.
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'onPreSubmitAlterDateWidget'],
            -2
        );

        // Date selector.
        $builder->add('date', DateType::class, [
            'data' => new \DateTime("now"),
        ]);

        // Timezone selector.
        $builder->add('timezone', TimezoneType::class, [
            'data' => date_default_timezone_get(),
        ]);

    }

    /**
     * Populate unset checkboxes with false value.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
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

    /**
     * Updates the checkboxes.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function onPreSubmitUpdateCheckboxes(FormEvent $event)
    {
        $form = $event->getForm();

        // Data contains only checkboxes.
        // If none of them was triggered - data is empty
        // but the form still needs to be processed.
        if (empty($data = $event->getData())) return;
        $alter_to_false = FALSE;
        $checked = '1';
        foreach ($data['checkboxes'] as $checkbox_field_name => &$value) {

            if ($value !== $checked || $alter_to_false) $value = FALSE;

            // TODO: check that next() works.
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

    }

    /**
     * Alter the date widget.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function onPreSubmitAlterDateWidget(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $date_options = [
            'data' => new \DateTime("now"),
        ];

        $old_date_type = $type = DateType::class;
        if (isset($data['checkboxes']['hours']) && $data['checkboxes']['hours']) {
            $type = DateTimeType::class;
            $date_options['with_minutes'] = false;
            $date_options['with_seconds'] = false;

            if (isset($data['checkboxes']['minutes']) && $data['checkboxes']['minutes']) {
                $date_options['with_minutes'] = true;
                if (isset($data['checkboxes']['seconds']) && $data['checkboxes']['seconds']) $date_options['with_seconds'] = true;
            }

        }

        // Date selector.
        $form->add('date', $type, $date_options);

        if (array_key_exists('date', $data["date"]) && array_key_exists('time', $data["date"])) {
            $old_date_type = DateTimeType::class;
        }

        $now_time = date('H:i:s', time());
        list($hours, $mins, $secs) = explode(':', $now_time);

        if ($old_date_type !== $type) {
            switch ($type) {
                case DateType::class:
                    $data['date'] = $data['date']['date'];
                    break;
                case DateTimeType::class:
                    $data['date'] = [
                        'date' => $data['date'],
                        'time' => [
                            'hour' => $hours,
                        ],
                    ];
                    break;
            }
        }

        if (isset($data['date']['time'])) {
            if (!isset($data['date']['time']['minute'])) $data['date']['time']['minute'] = $mins;
            if (!isset($data['date']['time']['second'])) $data['date']['time']['second'] = $secs;
        }

        $event->setData($data);
    }

}
