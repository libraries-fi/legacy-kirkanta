<?php

namespace Kirkanta\Form;

use Kirkanta\I18n\TranslatableInterface;
use Kirkanta\I18n\TranslatableTrait;
use Kirkanta\Util\RegionalLibraries;

class OrganisationWebLinkForm extends EntityForm
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'link_group',
            'options' => [
                'label' => $this->tr('Link group'),
            ],
            'attributes' => [
                'disabled' => true,
            ]
        ]);

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'url',
            'options' => [
                'label' => $this->tr('URL'),
            ]
        ]);
    }

    public function getTitle()
    {
        return $this->getObject()->isNew()
            ? $this->tr('New link')
            : $this->tr('Edit link');
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
                'name' => 'url',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    [
                        'name' => 'UriNormalize',
                        'options' => [
                            'default_scheme' => 'http',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'Uri',
                        'options' => [
                            'allowRelative' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
