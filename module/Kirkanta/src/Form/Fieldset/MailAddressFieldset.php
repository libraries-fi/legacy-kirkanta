<?php

namespace Kirkanta\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;
use Kirkanta\Entity\Address;
use Kirkanta\Entity\City;

class MailAddressFieldset extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        parent::init();

        $this->setObject(new Address);

        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ]);

        $this->add([
            'name' => 'enabled',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->getTranslator()->translate('Enable'),
            ]
        ]);

        $this->add([
            'name' => 'street',
            'options' => [
                'label' => $this->getTranslator()->translate('Street address'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'box_number',
            'options' => [
                'label' => $this->getTranslator()->translate('PO box number'),
            ],
        ]);

        $this->add([
            'name' => 'zipcode',
            'options' => [
                'label' => $this->getTranslator()->translate('Zipcode'),
            ],
        ]);

        $this->add([
            'name' => 'area',
            'options' => [
                'label' => $this->getTranslator()->translate('Post office'),
                'translatable' => true,
            ],
        ]);

        $translations = $this->get('translations')->getContainer();
        $this->setOption('translations', $translations);

        foreach ($this as $element) {
            $element->setOption('translations', $translations);
        }
    }

    public function setObject($object)
    {
        parent::setObject($object);

        if ($this->has('enabled')) {
            $this->get('enabled')->setChecked($object && !$object->isNew());
        }
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'enabled',
                'required' => false,
            ],
            [
                'name' => 'street',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
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
                'name' => 'box_number',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
                'validators' => [
                    [
                        'name' => 'Digits',
                    ],
                ],
            ],
            [
                'name' => 'zipcode',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'Digits',
                    ],
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 5
                        ]
                    ]
                ],
            ],
            [
                'name' => 'area',
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
                'name' => 'translations',
                'required' => false,
                'filters' => [
                    ['name' => 'Kirkanta\Filter\EmptyTranslations']
                ]
            ],
        ];
    }
}
