<?php

namespace Kirkanta\Form;

class ServiceReferenceForm extends ServiceForm
{
    public function init()
    {
        parent::init();
        $this->get('type')->setAttribute('disabled', true);
        $this->get('name')->setAttribute('disabled', true);
        // $this->get('short_description')->setAttribute('disabled', true);
        $this->get('helmet_type_priority')->setAttribute('disabled', true);

        $this->add([
            'name' => 'email',
            'options' => [
                'label' => $this->tr('Email'),
            ],
        ]);

        $this->add([
            'name' => 'phone_number',
            'options' => [
                'label' => $this->tr('Phone number'),
            ],
        ]);

        $this->add([
            'name' => 'website',
            'options' => [
                'label' => $this->tr('Website'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'helmet_priority',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Order in library details (HelMet)'),
                'value_options' => [
                    0 => $this->tr('n/a'),
                    1 => $this->tr('First'),
                    2 => $this->tr('Second'),
                    3 => $this->tr('Third'),
                    4 => $this->tr('Fourth'),
                    5 => $this->tr('Fifth'),
                    6 => $this->tr('Sixth'),
                ]
            ],
        ], ['priority' => -99]);

//         $this->actions = self::CANCEL_BUTTON;
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'type',
                'required' => false,
            ],
            [
                'name' => 'price',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'Null'],
                ],
                'validators' => [
                    ['name' => 'StringLength'],
                ],
            ],
            [
                'name' => 'for_loan',
                'required' => false,
                'filters' => [
                    ['name' => 'Boolean'],
                ],
            ],
            [
                'name' => 'email',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    ['name' => 'EmailAddress'],
                ],
            ],
            [
                'name' => 'website',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    ['name' => 'Uri'],
                ],
            ],
            [
                'name' => 'phone_number',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
            ],
            [
                'name' => 'helmet_priority',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
        ] + parent::getInputFilterSpecification();
    }
}
