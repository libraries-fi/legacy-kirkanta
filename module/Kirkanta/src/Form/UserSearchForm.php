<?php

namespace Kirkanta\Form;

class UserSearchForm extends AbstractSearchForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'username',
            'options' => [
                'label' => $this->tr('Name or email'),
            ],
        ]);

        $this->add([
            'name' => 'role',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->tr('Groups'),
                'empty_option' => $this->tr('All'),
                'target_class' => 'Kirkanta\Entity\Role',
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy'  => ['role_id' => 'asc']
                    ]
                ],
            ],
            'attributes' => [
                'multiple' => true,
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'username',
                'required' => false,
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
                'name' => 'role',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
        ];
    }
}
