<?php

namespace Kirkanta\Form\Fieldset;

use ArrayObject;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;
use Kirkanta\Entity\PeriodDay;
use Kirkanta\Hydrator\ArrayObjectHydrator;

class CustomDataFieldset extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        $this->setHydrator(new ArrayObjectHydrator);
        $this->setObject(new ArrayObject);

        $this->setOption('template', 'kirkanta/partial/custom-data.phtml');

        $this->add([
            'name' => 'title',
            'options' => [
                'label' => $this->getTranslator()->translate('Name'),
                'translatable' => true,
            ],
            'attributes' => [
                'placeholder' => $this->getTranslator()->translate('Informative name')
            ]
        ]);

        $this->add([
            'name' => 'id',
            'options' => [
                'label' => $this->getTranslator()->translate('Machine name'),
            ],
            'attributes' => [
                'placeholder' => $this->getTranslator()->translate('Internal ID')
            ]
        ]);

        $this->add([
            'name' => 'value',
            'type' => 'textarea',
            'options' => [
                'label' => $this->getTranslator()->translate('Value'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'title',
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
                            'max' => 60,
                        ],
                    ],
                ]
            ],
            [
                'name' => 'id',
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
                            'max' => 60,
                        ],
                    ],
                ]
            ],
            [
                'name' => 'value',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 255,
                        ],
                    ],
                ]
            ],
            [
                'name' => 'translations',
                'required' => true,
            ]
        ];
    }
}
