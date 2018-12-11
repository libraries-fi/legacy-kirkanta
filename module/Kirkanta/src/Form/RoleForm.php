<?php

namespace Kirkanta\Form;

class RoleForm extends EntityForm
{
    public function init()
    {
        $this->add([
            'name' => 'role_id',
            'options' => [
                'label' => $this->tr('Name'),
            ],
        ]);

        $this->add([
            'name' => 'parent',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->tr('Parent role'),
                'empty_option' => '',
                'target_class' => 'Kirkanta\Entity\Role',
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy'  => ['role_id' => 'asc']
                    ]
                ],
            ],
        ]);
        $this->add([
            'name' => 'description',
            'options' => [
                'label' => $this->tr('Description'),
            ],
            'attributes' => [
                'placeholder' => $this->tr('Describe the role\'s special purpose'),
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'role_id',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StringToLower'],
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
                'name' => 'parent',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
                'validators' => [
                    [
                        'name' => 'EntityNoRecursion',
                        'options' => [
                            'entity_id' => $this->getObject()->getId(),
                            'entity_class' => get_class($this->getObject()),
                            'getter' => 'getParent',
                        ],
                    ],
                ],
            ]
        ];
    }
}
