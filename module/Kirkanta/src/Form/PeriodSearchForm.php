<?php

namespace Kirkanta\Form;

use Kirkanta\Util\PeriodSections;

class PeriodSearchForm extends AbstractSearchForm
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
            'name' => 'section',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Section'),
                'value_options' => (new PeriodSections($this->getTranslator()))->getTypes(),
            ],
            'attributes' => [
                // 'multiple' => true,
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

        $this->add([
            'name' => 'withexpired',
            'type' => 'checkbox',
            'options' => [
                'fallback' => false,
                'label' => $this->tr('Show expired periods'),
            ],
        ]);

        // $this->add([
        //     'name' => 'noexpired',
        //     'type' => 'select',
        //     'options' => [
        //         // 'fallback' => false,
        //         'label' => $this->tr('Hide expired periods'),
        //         'empty_option' => $this->tr('Hide expired periods'),
        //         'value_options' => [
        //             // '' => $this->tr('Hide expired periods'),
        //             1 => $this->tr('Show all periods'),
        //         ]
        //     ],
        // ]);
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
                'name' => 'section',
                'required' => false,
            ],
            [
                'name' => 'withexpired',
                'required' => false,
            ],
            [
                'name' => 'group',
                'required' => false,
            ]
        ];
    }
}
