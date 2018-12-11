<?php

namespace Kirkanta\Export\Form;

use Kirkanta\Form\Form;
use Kirkanta\Util\OrganisationBranchTypes;
use Kirkanta\Util\OrganisationTypes;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AddressesForm extends Form implements InputFilterProviderInterface
{
    protected $form_id = 'export-addresses';

    public static function createInstance(ServiceLocatorInterface $services)
    {
        $translator = $services->get('MvcTranslator');
        return new static($translator);
    }

    public function __construct($translator)
    {
        parent::__construct($translator);
    }

    public function init()
    {
        $this->add([
            'name' => 'type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Type'),
                'value_options' => (new OrganisationTypes($this->getTranslator()))->getTypes(),
            ],
            'attributes' => [
                'multiple' => true,
                'size' => 8,
            ]
        ]);

        $this->add([
            'name' => 'branch_type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Branch type'),
                'value_options' => (new OrganisationBranchTypes($this->getTranslator()))->getTypes(),
            ],
            'attributes' => [
                'multiple' => true,
                'size' => 14,
            ]
        ]);

        $this->add([
            'type' => 'checkbox',
            'name' => 'group_by',
            'options' => [
                'label' => $this->tr('Group by city'),
            ]
        ]);

        $this->add([
            'type' => 'checkbox',
            'name' => 'with_coordinates',
            'options' => [
                'label' => $this->tr('With coordinates'),
            ]
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'options' => [
                'label' => $this->tr('Download'),
            ]
        ]);

        $this->get('type')->setValue(['library']);
        $this->get('branch_type')->setValue(['library', 'main_library']);
        $this->get('group_by')->setChecked(true);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'type',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => function($value) {
                                if ($value == ['']) {
                                    return [];
                                } else {
                                    return $value;
                                }
                            }
                        ]
                    ]
                ]
            ],
            [
                'name' => 'branch_type',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => function($value) {
                                if ($value == ['']) {
                                    return [];
                                } else {
                                    return $value;
                                }
                            }
                        ]
                    ]
                ]
            ]
        ];
    }
}
