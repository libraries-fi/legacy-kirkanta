<?php

namespace Kirkanta\Form;

class NotificationForm extends EntityForm
{
    protected $form_id = 'notification-form';

    public function init()
    {
        $this->add([
            'name' => 'title',
            'options' => [
                'label' => 'Title',
            ]
        ]);

        $this->add([
            'name' => 'message',
            'type' => 'textarea',
            'options' => [
                'label' => 'Message',
            ],
            'attributes' => [
                'rows' => 15,
                'cols' => 40,
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'title',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 10,
                            'max' => 200,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'message',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 10,
                        ],
                    ],
                ],
            ],
        ];
    }
}
