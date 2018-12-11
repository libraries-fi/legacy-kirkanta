<?php

namespace Kirkanta\Form;

use DoctrineORMModule\Form\Element\EntitySelect;
use Kirkanta\Entity\Organisation;

class PersonSearchForm extends AbstractSearchForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
            ],
        ]);

        $criteria = $this->isAllowed('admin')
         ? []
         : ['group' => array_map(function($r) {
             return $r->getId();
         }, $this->getCurrentUser()->getRoles())];

        $this->add([
            'name' => 'organisation',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->tr('Organisation'),
                'empty_option' => '',
                'target_class' => Organisation::class,
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => $criteria,
                        'orderBy'  => ['name' => 'asc']
                    ]
                ]
            ]
        ]);

        if ($this->isAllowed('admin')) {
            $this->add([
                'name' => 'group',
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
                    'id' => 'input-select-group',
                    'multiple' => true,
                ],
            ]);
        }
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'name',
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
                'name' => 'organisations',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
            [
                'name' => 'organisation',
                'required' => false,
            ],
            [
                'name' => 'group',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
        ];
    }
}
