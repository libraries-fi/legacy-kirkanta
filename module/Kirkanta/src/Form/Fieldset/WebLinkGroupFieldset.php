<?php

namespace Kirkanta\Form\Fieldset;

use Kirkanta\Entity\ConsortiumWebLink;
use Kirkanta\Entity\ConsortiumWebLinkGroup;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;

class WebLinkGroupFieldset extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        parent::init();

        $this->setObject(new ConsortiumWebLinkGroup);
        $this->setOption('template', 'kirkanta/partial/collection-row.phtml');

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->getTranslator()->translate('Name'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'identifier',
            'options' => [
                'label' => $this->getTranslator()->translate('Identifier'),
                'info' => $this->getTranslator()->translate('Machine-readable identifier.'),
            ],
        ]);

        $this->add([
            'name' => 'description',
            'options' => [
                'label' => $this->getTranslator()->translate('Description'),
                'info' => $this->getTranslator()->translate('Internal description for the group.'),
            ],
        ]);

        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ]);
    }

    public function getTitle()
    {
        return $this->get('name')->getValue() ?: $this->getTranslator()->translate('New link group');
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
                'name' => 'description',
                'required' => false,
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
                'name' => 'translations',
                'required' => true,
            ]
        ];
    }
}
