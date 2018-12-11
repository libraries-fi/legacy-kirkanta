<?php

namespace Kirkanta\Form;

use Kirkanta\Util\ServiceTypes;

class ServiceTypeSearchForm extends AbstractSearchForm
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
            'name' => 'type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Type'),
                'empty_option' => $this->tr('All'),
                'value_options' => (new ServiceTypes($this->getTranslator()))->getTypes(),
            ],
            'attributes' => [
//                 'multiple' => true,
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
                'name' => 'type',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
            ],
        ];
    }
}
