<?php

namespace Kirkanta\Form\Fieldset;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use DoctrineORMModule\Form\Element\EntitySelect;
use Kirkanta\Entity\ConsortiumWebLink;
use Kirkanta\Entity\ConsortiumWebLinkGroup;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;
// use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class WebLinkFieldset extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface, ObjectManagerAwareInterface
{
    use ProvidesObjectManager;
    use TranslatorAwareTrait;

    public function setOptions($options)
    {
        parent::setOptions($options);

        $this->setOption('template', 'kirkanta/partial/collection-row.phtml');

        // Options are set in FormFactory after init() is executed, so we initialize the select box here.
        $this->get('link_group')->setOption('find_method', [
            'name'   => 'findBy',
            'params' => [
                'criteria' => [
                    'consortium' => $this->getOption('consortium'),
                ],
                'orderBy'  => ['name' => 'asc']
            ]
        ]);
    }

    public function init()
    {
        parent::init();

        $this->setObject(new ConsortiumWebLink);
        $this->setOption('template', 'kirkanta/partial/collection-row.phtml');

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->getTranslator()->translate('Name'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'url',
            'type' => 'url',
            'options' => [
                'label' => $this->getTranslator()->translate('URL'),
            ],
            'attributes' => [
                'placeholder' => 'https://...',
            ]
        ]);

        $this->add([
            'name' => 'link_group',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->getTranslator()->translate('Group'),
                'empty_option' => '',
                'target_class' => ConsortiumWebLinkGroup::class,
            ],
            'attributes' => [
                'class' => 'link-group-select',
            ]
        ]);

        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ]);
    }

    public function getTitle()
    {
        return $this->get('name')->getValue() ?: $this->getTranslator()->translate('New link');
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
            [
                'name' => 'link_group',
                'required' => true,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
                'validators' => [
                    ['name' => 'NotEmpty'],
                ],
            ],
            [
                'name' => 'translations',
                'required' => true,
            ]
        ];
    }
}
