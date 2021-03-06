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
use Symfony\Component\OptionsResolver\OptionsResolver;

class GifFormType extends AbstractType
{

    /**
     * @var array
     */
    protected $checkboxes;

    /**
     * @var null
     */
    protected $ajax = null;

    /**
     * GifFormType constructor.
     */
    public function __construct()
    {
        $this->checkboxes = [
            'hours',
            'minutes',
            'seconds'
        ];
    }

    /**
     * @param null $ajax
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;
    }

    /**
     * @link https://stackoverflow.com/questions/36999017/symfony-3-createform-with-construct-parameters
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults( [
            'ajax' => null,
        ] );
    }

    /**
     * Creates date widget default options.
     *
     * @return array
     */
    protected function createDateOptions()
    {
        return [
            'data' => new \DateTime("now"),
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Set up ajax value for further use in the events.
        $this->setAjax($options['ajax']);

        // Checkboxes.
        $builder->add(
            $builder->create('checkboxes', FormType::class, array('inherit_data' => true))
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
        $builder->add('date', DateType::class, $this->createDateOptions());

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
        $data = $event->getData();

        // Flag to alter all the following checkboxes to unchecked.
        $alter_to_false = FALSE;

        // Value if checked.
        $checked = '1';
        foreach ($data['checkboxes'] as $checkbox_field_name => &$value) {

            // e.g. if Hours is unchecked, uncheck Minutes and Seconds anyway.
            if ($value !== $checked || $alter_to_false) $value = FALSE;

            // TODO: check that next() works.
            if ($next_checkbox_field_name = next($this->checkboxes)) {
                $next_config = $form->get('checkboxes')->get($next_checkbox_field_name)->getConfig();
                $next_options = $next_config->getOptions();
                $next_options['disabled'] = FALSE;

                // Alter all the following checkboxes to be unchecked.
                if ($value !== $checked) {
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
    public function onPreSubmitAlterDateWidget(FormEvent $event/*, $a, $b*/)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // Date default options.
        $date_options = $this->createDateOptions();

        // Default type of the Date widget.
        $old_date_type = $type = DateType::class;

        // If $data contains both 'date' and 'time' keys - old widget type is DateTimeType.
        if (array_key_exists('date', $data["date"]) && array_key_exists('time', $data["date"])) {
            $old_date_type = DateTimeType::class;
        }

        // If Hours are set - change DateType to DateTimeType.
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

        // Get current time.
        $now_time = date('H:i:s', time());
        list($hours, $mins, $secs) = explode(':', $now_time);

        // If the widget has changed.
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

        // We need this options to be set up only while ajax calls.
        if (isset($data['date']['time']) && $this->ajax) {
            if (!isset($data['date']['time']['minute'])) $data['date']['time']['minute'] = $mins;
            if (!isset($data['date']['time']['second'])) $data['date']['time']['second'] = $secs;
        }

        $event->setData($data);
    }

}
