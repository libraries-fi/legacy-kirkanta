<?php

namespace Kirkanta\Form\Fieldset;

use ArrayObject;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;
use Kirkanta\Entity\PeriodDay;
use Kirkanta\Hydrator\ArrayObjectHydrator;

class OpeningTimesFieldset extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        $this->setHydrator(new ArrayObjectHydrator);
        $this->setObject(new ArrayObject);

        $this->add([
            'name' => 'opens',
            'type' => 'time',
            'options' => [
                'label' => $this->getTranslator()->translate('Opens'),
                'format' => 'H:i',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'closes',
            'type' => 'time',
            'options' => [
                'label' => $this->getTranslator()->translate('Closes'),
                'format' => 'H:i',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'opens',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'DateTimeFormatter',
                        'options' => [
                            'format' => 'H:i',
                        ],
                    ],
                    [
                        'name' => 'callback',
                        'options' => [
                            'callback' => [$this, 'filterClosedDay']
                        ]
                    ],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    [
                        'name' => 'Date',
                        'options' => [
                            'format' => 'H:i',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'closes',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'DateTimeFormatter',
                        'options' => [
                            'format' => 'H:i',
                        ],
                    ],
                    [
                        'name' => 'callback',
                        'options' => [
                            'callback' => [$this, 'filterClosedDay']
                        ]
                    ],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    [
                        'name' => 'Date',
                        'options' => [
                            'format' => 'H:i',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function filterClosedDay($value)
    {
        return $value;
    }
}
