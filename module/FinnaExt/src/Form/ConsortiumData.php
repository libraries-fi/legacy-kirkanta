<?php

namespace Kirkanta\Finna\Form;

use DoctrineORMModule\Form\Element\EntitySelect;
use Kirkanta\Finna\Entity\ConsortiumData as ConsortiumDataEntity;
use Kirkanta\Entity\Organisation;
use Kirkanta\Form\TranslatorAwareFieldsetTrait;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class ConsortiumData extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
{
    use TranslatorAwareFieldsetTrait;

    public function setOptions($options)
    {
        parent::setOptions($options);

        // Options are set in FormFactory after init() is executed, so we initialize the select box here.
        $this->get('service_point')->setOption('find_method', [
            'name'   => 'findByConsortium',
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

        $this->setObject(new ConsortiumDataEntity);
        $this->setOption('label', 'FINNA');

        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ]);

        $this->add([
            'name' => 'finna_id',
            'options' => [
                'label' => $this->tr('Finna ID')
            ]
        ]);

        $this->add([
            'name' => 'finna_coverage',
            'type' => 'number',
            'options' => [
                'label' => $this->tr('Finna coverage')
            ],
            'attributes' => [
                'placeholder' => 'Percentage (%)'
            ]
        ]);

        $this->add([
            'name' => 'service_point',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->getTranslator()->translate('Default service point'),
                'empty_option' => '',
                'target_class' => Organisation::class,
            ],
            'attributes' => [
                'class' => 'link-group-select',
            ]
        ]);

        $this->add([
            'name' => 'usage_info',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Usage info'),
                'translatable' => true,
            ],
            'attributes' => [
                'class' => 'richtext',
                'rows' => 13,
                'cols' => 40,
            ]
        ]);

        $this->add([
            'name' => 'notification',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Notification'),
                'translatable' => true,
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'finna_id',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 2,
                            'max' => 100,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'finna_coverage',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull'],
                ],
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'service_point',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull'],
                ],
            ],
            [
                'name' => 'usage_info',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'notification',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                        ],
                    ],
                ],
            ],
        ];
    }
}
