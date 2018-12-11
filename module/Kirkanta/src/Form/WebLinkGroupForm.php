<?php

namespace Kirkanta\Form;

use Kirkanta\I18n\TranslatableInterface;
use Kirkanta\I18n\TranslatableTrait;
use Kirkanta\Util\RegionalLibraries;

abstract class WebLinkGroupForm extends EntityForm
{
    public function init()
    {
        parent::init();
    }

    public function getTitle()
    {
        return $this->getObject()->isNew()
            ? $this->tr('New link group')
            : $this->tr('Edit link group');
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
                'name' => 'identifier',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => function($value) {
                                $value = strtolower($value);
                                $value = preg_replace('/\W+/', '_');
                                return $value;
                            }
                        ]
                    ]
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
        ];
    }
}
