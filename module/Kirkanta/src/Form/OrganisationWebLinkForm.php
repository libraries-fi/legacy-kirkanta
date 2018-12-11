<?php

namespace Kirkanta\Form;

use DoctrineORMModule\Form\Element\EntitySelect;
use Kirkanta\Entity\OrganisationWebLinkGroup;
use Kirkanta\I18n\TranslatableInterface;
use Kirkanta\I18n\TranslatableTrait;
use Kirkanta\Util\RegionalLibraries;

class OrganisationWebLinkForm extends EntityForm
{
    public function init()
    {
        parent::init();

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

        $this->add([
            'name' => 'link_group',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->getTranslator()->translate('Group'),
                'empty_option' => '',
                'target_class' => OrganisationWebLinkGroup::class,
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [
                            'organisation' => $this->getObject()->getOrganisation(),
                        ],
                        'orderBy'  => ['name' => 'asc']
                    ]
                ],
            ],
            'attributes' => [
                'class' => 'link-group-select',
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
