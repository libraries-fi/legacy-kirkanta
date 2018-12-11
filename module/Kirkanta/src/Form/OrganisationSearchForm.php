<?php

namespace Kirkanta\Form;

use Kirkanta\Util\OrganisationBranchTypes;
use Kirkanta\Util\OrganisationTypes;
use Zend\Filter\ToNull;

class OrganisationSearchForm extends AbstractSearchForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
            ]
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Type'),
                'value_options' => (new OrganisationTypes($this->getTranslator()))->getTypes(),
            ]
        ]);

        $this->add([
            'name' => 'branch_type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Branch type'),
                'required' => false,
                'value_options' => (new OrganisationBranchTypes($this->getTranslator()))->getTypes(),
            ]
        ]);

        $this->add([
            'name' => 'state',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Published'),
                'value_options' => [
                    $this->tr('No'),
                    $this->tr('Yes'),
                ],
            ],
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
                'name' => 'type',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
            ],
            [
                'name' => 'branch_type',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
            ],
            [
                'name' => 'state',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
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
