<?php

namespace Kirkanta\Form;

class AccessibilityFeatureForm extends EntityForm
{
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
                'name' => 'description',
                'required' => false
            ],
        ];
    }
}
