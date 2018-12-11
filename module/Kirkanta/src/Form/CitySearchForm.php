<?php

namespace Kirkanta\Form;

class CitySearchForm extends AbstractSearchForm
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

        $this->add([
            'name' => 'region',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->tr('Regions'),
                'empty_option' => '',
                'target_class' => 'Kirkanta\Entity\Region',
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy'  => ['name' => 'asc']
                    ]
                ],
            ],
            'attributes' => [
                'multiple' => true,
            ],
        ]);

        $this->add([
            'name' => 'provincial_library',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->tr('Provincial Library'),
                'empty_option' => '',
                'target_class' => 'Kirkanta\Entity\ProvincialLibrary',
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy'  => ['name' => 'asc']
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
                'name' => 'region',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
            [
                'name' => 'provincial_library',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
        ];
    }
}
