<?php

namespace Kirkanta\Form;

use Kirkanta\Util\ServiceTypes;

class ServiceTypeForm extends EntityForm
{
    protected $form_id = 'service-form';

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
            'options' => [
                'label' => $this->tr('Description'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Type'),
                'empty_option' => '',
                'value_options' => (new ServiceTypes($this->getTranslator()))->getTypes(),
            ],
        ]);

        $this->add([
            'name' => 'helmet_type_priority',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Display on Helmet.fi'),
            ],
        ], ['priority' => -100]);
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
                            'min' => 2,
                            'max' => 200,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'type',
                'required' => true,
            ],
            [
                'name' => 'description',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'Null'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 200
                        ]
                    ],
                ],
            ],
            [
                'name' => 'helmet_type_priority',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
        ];
    }
}
