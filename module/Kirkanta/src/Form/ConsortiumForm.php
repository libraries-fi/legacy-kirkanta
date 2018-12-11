<?php

namespace Kirkanta\Form;

use Kirkanta\Form\Element\RoleSelect;

class ConsortiumForm extends EntityForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'state',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('State'),
                'value_options' => [
                    0 => $this->tr('Hidden'),
                    1 => $this->tr('Published'),
                ],
            ]
        ]);

        if ($this->isAllowed('admin')) {
            $this->add([
                'name' => 'group',
                'type' => RoleSelect::class,
                'options' => [
                    'label' => $this->tr('Owner'),
                ]
            ]);
        }

        $this->add([
            'type' => 'text',
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'homepage',
            'options' => [
                'label' => $this->tr('Homepage'),
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
            'name' => 'description',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Description'),
                'translatable' => true,
            ],
            'attributes' => [
                'class' => 'richtext',
                'rows' => 13,
                'cols' => 40,
            ]
        ]);

        $this->add([
            'name' => 'special',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Finna organisation'),
                'info' => $this->tr('Enable this option if the consortium is not allowed for regular municipality libraries.'),
            ],
        ]);

        $this->add([
            'name' => 'logo_file',
            'type' => 'file',
            'options' => [
                'label' => $this->tr('Logo'),
            ],
        ]);

        $this->add([
            'name' => 'logo',
            'type' => $this->getObject()->getLogo() ? 'text' : 'hidden',
            'options' => [
                'label' => $this->tr('Logo'),
            ],
        ]);

        if ($this->getObject()->getLogo()) {
            $this->get('logo')->setOption('view', 'KirkantaFormPicture');
            $this->get('logo_file')->setLabel($this->tr('Change logo'));
        }

        $this->add([
            'name' => 'finna_data',
            'type' => \Kirkanta\Finna\Form\ConsortiumData::class,
            'options' => [
                'consortium' => $this->getObject(),
            ]
        ]);

        // var_dump($this->getFormFactory());

        $this->add([
            'name' => 'links',
            'type' => 'collection',
            'options' => [
                'label' => $this->tr('Links'),
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => [
                    'type' => Fieldset\WebLinkFieldset::class,
                    'options' => [
                        'consortium' => $this->getObject(),
                    ],
                ]
            ]
        ]);

        $this->add([
            'name' => 'link_groups',
            'type' => 'collection',
            'options' => [
                'label' => $this->tr('Link groups'),
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => [
                    'type' => Fieldset\WebLinkGroupFieldset::class,
                ]
            ]
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
                'name' => 'homepage',
                'required' => false,
                'filters' => [
                    ['name' => 'UriNormalize'],
                ],
                'validators' => [
                    [
                        'name' => 'Uri',
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
                'name' => 'logo',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
            ],
            [
                'name' => 'logo_file',
                'required' => false,
                'type' => 'Zend\InputFilter\FileInput',
                'validators' => [
                    ['name' => 'FileUploadFile'],
                    [
                        'name' => 'FileExtension',
                        'options' => [
                            'extension' => ['gif', 'jpg', 'jpeg', 'png'],
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
                    [
                        'name' => 'ScaleUploadedPicture',
                        'options' => [
                            'sizes' => ['small', 'medium']
                        ]
                    ],
                    [
                        'name' => 'CopyToField',
                        'options' => [
                            'form' => $this,
                            'field' => 'logo',
                        ]
                    ],
                ],
            ],
            [
                'name' => 'translations',
                'required' => false,
            ],
        ];
    }
}
