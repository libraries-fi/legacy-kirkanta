<?php

namespace Kirkanta\Form\Fieldset;

use ArrayObject;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;
use Kirkanta\Entity\PeriodDay;
use Kirkanta\Hydrator\ArrayObjectHydrator;

class PeriodDayFieldset extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        $this->setHydrator(new ArrayObjectHydrator);
        $this->setObject(new ArrayObject);
        $this->setOption('template', 'kirkanta/partial/period-days-row.phtml');

        $this->add([
            'name' => 'times',
            'type' => 'collection',
            'options' => [
                'should_create_template' => true,
                'target_element' => ['type' => OpeningTimesFieldset::class],
                'count' => 0,
                'allow_add' => true,
                'allow_remove' => true,
                'template_placeholder' => '--index--',
                // 'hydrator' => new ArrayObjectHydrator,
            ]
        ]);

        $this->add([
            'name' => 'closed',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->getTranslator()->translate('Closed'),
            ],
        ]);

        $this->add([
            'name' => 'info',
            'options' => [
                'label' => $this->getTranslator()->translate('Description'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ]);

        $this->get('times')->setHydrator(new ArrayObjectHydrator);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'closed',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
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
                            'max' => 100,
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
