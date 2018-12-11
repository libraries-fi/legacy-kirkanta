<?php

namespace Kirkanta\Form;

use DoctrineORMModule\Form\Element\EntitySelect;
use Kirkanta\Entity\Organisation;
use Kirkanta\Util\PersonQualities;

class PersonForm extends EntityForm
{
    public function init()
    {
        parent::init();

        $criteria = $this->getCurrentUser()->isAdmin()
         ? []
         : ['group' => array_map(function($r) {
             return $r->getId();
         }, $this->getCurrentUser()->getRoles())];

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

        $this->add([
            'name' => 'organisation',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->tr('Organisation'),
                'empty_option' => '',
                'target_class' => Organisation::class,
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => $criteria,
                        'orderBy'  => ['name' => 'asc']
                    ]
                ]
            ]
        ]);

        $this->add([
            'name' => 'first_name',
            'options' => [
                'label' => $this->tr('First name'),
            ],
        ]);

        $this->add([
            'name' => 'last_name',
            'options' => [
                'label' => $this->tr('Last name'),
            ],
        ]);

        $this->add([
            'name' => 'job_title',
            'options' => [
                'label' => $this->tr('Job title'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'responsibility',
            'options' => [
                'label' => $this->tr('Responsibility'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'qualities',
            'type' => 'select',
            'options' => [
                'use_hidden_element' => true,
                'label' => $this->tr('Qualities'),
                'value_options' => (new PersonQualities($this->getTranslator()))->getQualities(),
            ],
            'attributes' => [
                'multiple' => true,
                'size' => 8,
            ],
        ]);

        $this->add([
            'name' => 'email',
            'options' => [
                'label' => $this->tr('Email address'),
            ],
        ]);

        $this->add([
            'name' => 'email_public',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Email address is public'),
            ],
        ]);

        $this->add([
            'name' => 'phone',
            'options' => [
                'label' => $this->tr('Phone number'),
            ],
        ]);

        $this->add([
            'name' => 'is_head',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Head of the organisation'),
            ],
        ]);

        $this->add([
            'name' => 'url',
            'options' => [
                'label' => $this->tr('Link to website'),
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

        if ($this->getObject()->getPicture()) {
            $this->get('picture')->setOption('view', 'KirkantaFormPicture');
            $this->get('file')->setLabel($this->tr('Change picture'));
        }
    }

    public function prefixUrl($url)
    {
        if ($url and !preg_match('#^\w+\://#', $url)) {
            $url = 'http://' . $url;
        }
        return $url;
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'first_name',
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 2,
                            'max' => 200,
                        ]
                    ]
                ]
            ],
            [
                'name' => 'last_name',
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 2,
                            'max' => 200,
                        ]
                    ]
                ]
            ],
            [
                'name' => 'email',
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StringToLower'],
                ],
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                    ]
                ],
            ],
            [
                'name' => 'qualities',
                'required' => false,
            ],
            [
                'name' => 'url',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => [$this, 'prefixUrl'],
                        ],

                    ],
                ],
                'validators' => [
                    [
                        'name' => 'Uri',
                    ]
                ],
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
            ],
            [
                'name' => 'organisation',
                'required' => true,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
            [
                'name' => 'email_public',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                ],
            ],
            [
                'name' => 'is_head',
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
        ];
    }
}
