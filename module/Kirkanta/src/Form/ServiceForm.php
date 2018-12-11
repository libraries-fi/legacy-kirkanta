<?php

namespace Kirkanta\Form;

use Kirkanta\Util\ServiceTypes;

class ServiceForm extends EntityForm
{
    protected $form_id = 'service-form';

    public function init()
    {
        parent::init();

        if ($this->getObject()->getTemplate()) {
            $this->add([
                'name' => 'template_info',
                'type' => Fieldset\ServiceBaseInfo::class,
                'options' => [
                    'label' => $this->tr('Base info')
                ]
            ]);
            $this->get('template_info')->setObject($this->getObject()->getTemplate());
        }

        if (!$this->getObject()->getTemplate() || $this->isAllowed('override-service-template')) {
            $this->add([
                'name' => 'template',
                'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
                'options' => [
                    'label' => $this->tr('Service type'),
                    'empty_option' => '',
                    'target_class' => 'Kirkanta\Entity\ServiceType',
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

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
                'info' => $this->tr('Only fill when you need to alter the standard name'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'short_description',
            'type' => 'text',
            'options' => [
                'label' => $this->tr('Short description'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Description'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'price',
            'options' => [
                'label' => $this->tr('Price of the service'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'for_loan',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Available for loan'),
            ],
        ]);

        $this->add([
            'name' => 'file',
            'type' => 'file',
            'options' => [
                'label' => $this->tr('Picture'),
            ],
        ]);

        $this->add([
            'name' => 'picture',
            'type' => $this->getObject()->getPicture() ? 'text' : 'hidden',
            'options' => [
                'label' => $this->tr('Picture'),
            ],
        ]);

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

        if ($this->getObject()->getPicture()) {
            $this->get('picture')->setOption('view', 'KirkantaFormPicture');
            $this->get('file')->setLabel($this->tr('Change picture'));
        }
    }

    public function setObject($object)
    {
        parent::setObject($object);

        if ($this->has('template_info') && $object->getTemplate()) {
            $template = $object->getTemplate();
            $fieldset = $this->get('template_info');
            $fieldset->populateValues($fieldset->getHydrator()->extract($template));
        }
    }

    public function getInputFilterSpecification()
    {
        return [

            [
                'name' => 'type',
                'required' => false,
            ],
            [
                'name' => 'standard_name',
                'required' => false,
            ],
            [
                'name' => 'template_id',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                ],
            ],
            [
                'name' => 'name',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
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
                'name' => 'short_description',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 120,
                        ]
                    ],
                ],
            ],
            [
                'name' => 'description',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 2000,
                        ]
                    ],
                ],
            ],
            [
                'name' => 'price',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 140,
                        ]
                    ],
                ],
            ],
            [
                'name' => 'state',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                ],
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => -1,
                            'max' => 1,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'for_loan',
                'required' => false,
                'filters' => [
                    ['name' => 'Boolean'],
                ],
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'type' => 'null',
                        ]
                    ]
                ]
            ],
            [
                'name' => 'file',
                'required' => false,
                'type' => 'Zend\InputFilter\FileInput',
                'validators' => [
                    ['name' => 'FileUploadFile'],
                    [
                        'name' => 'FileExtension',
                        'options' => [
                            'extension' => ['jpg', 'jpeg', 'png'],
                        ]
                    ]
                ],
                'filters' => [
                    [
                        'name' => 'FileRenameUpload',
                        'options' => [
                            'target' => 'public/files/images/original',
                            'randomize' => true,
                            'use_upload_name' => true,
                            'use_upload_extension' => true,
                        ]
                    ],
                    ['name' => 'ScaleUploadedPicture'],
                    [
                        'name' => 'CopyToField',
                        'options' => [
                            'form' => $this,
                            'field' => 'picture',
                        ]
                    ],
                ],
            ],
            [
                'name' => 'picture',
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
        ];
    }
}
