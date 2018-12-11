<?php

namespace Kirkanta\Form;

class RegionForm extends EntityForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'slug',
            'options' => [
                'label' => $this->tr('Slug'),
                'translatable' => true,
            ]
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
                            'max' => 100,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'slug',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 2,
                            'max' => 30,
                        ],
                    ],
                    [
                        'name' => 'UniqueValue',
                        'options' => [
                            'entity_id' => $this->getObject()->getId(),
                            'entity_class' => get_class($this->getObject()),
                            'field' => 'slug',
                        ]
                    ]
                ],
            ],
        ];
    }
}
