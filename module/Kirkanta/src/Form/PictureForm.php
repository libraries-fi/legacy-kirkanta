<?php

namespace Kirkanta\Form;

use Zend\Filter\ToNull;
use Zend\Validator\NotEmpty;

class PictureForm extends EntityForm
{
    protected $form_id = 'picture-form';

    public function getTitle()
    {
        return $this->getObject()->isNew()
            ? $this->tr('New picture')
            : $this->get('name')->getValue();
    }

    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'filename',
            'type' => $this->getObject()->getFilename() ? 'text' : 'file',
            'options' => [
                'label' => $this->tr('File'),
                'picsize' => 'medium',
            ],
        ]);

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'author',
            'options' => [
                'label' => $this->tr('Author'),
            ],
        ]);

        $this->add([
            'name' => 'year',
            'options' => [
                'label' => $this->tr('Year'),
            ],
            'attributes' => [
                'type' => 'number',
            ],
        ]);

        $this->add([
            'name' => 'default',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Use as default picture'),
            ],
        ]);

        if ($this->getObject()->getFilename()) {
            $this->get('filename')->setOption('label', null);
            $this->get('filename')->setOption('view', 'KirkantaFormPicture');
            $this->get('filename')->setAttribute('disabled', true);
        }
    }

    public function getInputFilterSpecification()
    {
        $filter = [
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
                            'min' => 3,
                            'max' => 200,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'author',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 200,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'description',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'year',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => 1700,
                            'max' => 2100,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'default',
                'required' => false,
                'filters' => [
                    ['name' => 'Boolean'],
                ],
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'type' => NotEmpty::NULL,
                        ]
                    ]
                ]
            ],
        ];

        if (!$this->getObject()->getFilename()) {
            $filter[] = [
                'name' => 'filename',
                'required' => true,
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
                    ['name' => 'ScaleUploadedPicture']
                ],
            ];
        }

        return $filter;
    }
}
