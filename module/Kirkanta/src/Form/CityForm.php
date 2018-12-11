<?php

namespace Kirkanta\Form;

use Kirkanta\I18n\TranslatableInterface;
use Kirkanta\I18n\TranslatableTrait;
use Kirkanta\Util\RegionalLibraries;

class CityForm extends EntityForm
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

        $this->add([
            'name' => 'region',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->tr('Region'),
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
        ]);

        $this->add([
            'name' => 'consortium',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->tr('Consortium'),
                'empty_option' => '',
                'target_class' => 'Kirkanta\Entity\Consortium',
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy'  => ['name' => 'asc']
                    ]
                ],
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
            [
                'name' => 'provincial_library',
                'required' => true,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
            [
                'name' => 'consortium',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
            [
                'name' => 'region',
                'required' => true,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
        ];
    }
}
