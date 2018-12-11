<?php

namespace Kirkanta\Form;

use Kirkanta\Form\Element\PeriodDayCollection;
use Kirkanta\Util\PeriodSections;
use Zend\Form\ElementInterface;

class PeriodForm extends EntityForm
{
    protected $form_id = 'period-form';

    public function getTitle()
    {
        return $this->getObject()->isNew()
            ? $this->tr('New period')
            : $this->get('name')->getValue();
    }

    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Description'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'section',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Section'),
                'value_options' => (new PeriodSections($this->getTranslator()))->getTypes(),
            ],
        ]);

        $this->add([
            'name' => 'continuous',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Continuous period'),
            ]
        ]);

        $this->add([
            'name' => 'valid_from',
            'type' => 'date',
            'options' => [
                'label' => $this->tr('Valid from'),
            ],
        ]);

        $this->add([
            'name' => 'valid_until',
            'type' => 'date',
            'options' => [
                'label' => $this->tr('Valid until'),
            ],
        ]);

        $this->add([
            'name' => 'days',
            'type' => PeriodDayCollection::class,
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'name',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 200,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'valid_from',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'DateTimeFormatter',
                        'options' => [
                            'format' => 'Y-m-d',
                        ],
                    ],
                ],
                'validators' => [
                    ['name' => 'Date'],
                ],
            ],
            [
                'name' => 'valid_until',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'DateTimeFormatter',
                        'options' => [
                            'format' => 'Y-m-d',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'Date',
                    ],
                ],
            ],
            [
                'name' => 'continuous',
                'required' => false,
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 1,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'description',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
        ];
    }

    public function bind($object, $flags = self::VALUES_NORMALIZED)
    {
        parent::bind($object);

        if ($this->has('continuous') and $this->getObject()->isNew()) {
            $this->get('continuous')->setChecked(false);
        }
        return $this;
    }
}
