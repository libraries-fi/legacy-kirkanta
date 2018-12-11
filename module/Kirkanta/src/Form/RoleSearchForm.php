<?php

namespace Kirkanta\Form;

class RoleSearchForm extends AbstractSearchForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'role_id',
            'options' => [
                'label' => $this->tr('Name'),
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'role_id',
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
        ];
    }
}
