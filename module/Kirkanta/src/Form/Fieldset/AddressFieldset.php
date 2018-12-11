<?php

namespace Kirkanta\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;
use Kirkanta\Entity\Address;
use Kirkanta\Entity\City;

class AddressFieldset extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
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
            'name' => 'street',
            'options' => [
                'label' => $this->getTranslator()->translate('Street address'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'zipcode',
            'options' => [
                'label' => $this->getTranslator()->translate('Zipcode'),
            ],
        ]);

        $this->add([
            'name' => 'city',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'options' => [
                'label' => $this->getTranslator()->translate('City'),
                'empty_option' => '',
                'target_class' => City::class,
                'find_method'    => [
                    'name'   => 'findAll',
                    'params' => [
                        'criteria' => [],
                        'orderBy'  => ['name' => 'ASC']
                    ]
                ],
            ],
        ]);

        $this->add([
            'name' => 'area',
            'options' => [
                'label' => $this->getTranslator()->translate('Post office'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'info',
            'options' => [
                'label' => $this->getTranslator()->translate('Info'),
                'info' => $this->getTranslator()->translate('Optional description to guide visitors.'),
                'translatable' => true,
            ],
        ]);

        $this->setOption('required', false);

        $translations = $this->get('translations')->getContainer();
        $this->setOption('translations', $translations);

        foreach ($this as $element) {
            $element->setOption('translations', $translations);
        }
    }

    public function setOptions($options)
    {
        if (isset($options['optional'])) {
            $this->setOptional($options['optional']);
            unset($options['optional']);
        }

        parent::setOptions($options);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'street',
                'required' => true,
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
                'name' => 'area',
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
                'name' => 'info',
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
                            'max' => 120,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'zipcode',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
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
                'name' => 'city',
                'required' => true,
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
