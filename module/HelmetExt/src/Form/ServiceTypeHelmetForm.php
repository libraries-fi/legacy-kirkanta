<?php

namespace Kirkanta\Helmet\Form;

use Kirkanta\Form\EntityForm;
use Kirkanta\Util\ServiceTypes;

class ServiceTypeHelmetForm extends EntityForm
{
    protected $form_id = 'service-type-helmet-form';

    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
                'label' => $this->tr('Name'),
            ],
            'attributes' => [
                'disabled' => true
            ]
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Type'),
                'empty_option' => '',
                'value_options' => (new ServiceTypes($this->getTranslator()))->getTypes(),
            ],
            'attributes' => [
                'disabled' => true
            ]
        ]);

        $this->add([
            'name' => 'helmet_type_priority',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Display on Helmet.fi'),
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'type',
                'required' => false,
            ],
            [
                'name' => 'name',
                'required' => false,
            ],
            [
                'name' => 'helmet_type_priority',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
        ];
    }
}
