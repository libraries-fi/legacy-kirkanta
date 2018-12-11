<?php

namespace Kirkanta\Form;

class UserForm extends EntityForm
{
    public function getTitle()
    {
        return $this->getObject()->isNew()
            ? $this->tr('New user')
            : sprintf($this->tr('Edit user #%d'), $this->getObject()->getId());
    }

    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'username',
            'options' => [
                'label' => $this->tr('Username'),
            ]
        ]);

        $this->add([
            'name' => 'email',
            'options' => [
                'label' => $this->tr('Email'),
            ]
        ]);

        $this->add([
            'name' => 'role',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->tr('User Group'),
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
            'name' => 'auth_password',
            'options' => [
                'label' => $this->tr('Change password'),
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        $filter = [
            [
                'name' => 'username',
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
                'name' => 'email',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StringToLower'],
                ],
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                    ],
                ],
            ],
        ];

        if ($this->has('auth_password')) {
            $filter[] = [
                'name' => 'auth_password',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 8,
                        ],
                    ],
//                     [
//                         'name' => 'Callback',
//                         'options' => [
//                             'callback' => [$this, 'validatePassword'],
//                         ],
//                     ]
                ],
            ];
        }

        return $filter;
    }

//     public function validatePassword($value)
//     {
//
//     }
}
