<?php

namespace Kirkanta\Form\Fieldset;

use Kirkanta\Util\ServiceTypes;
use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class ServiceBaseInfo extends Fieldset implements InputFilterProviderInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        $this->setHydrator(new ClassMethodsHydrator);
        
        $this->add([
            'type' => 'Kirkanta\I18n\Form\Element\Translations',
            'name' => 'translations',
        ]);

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->getTranslator()->translate('Standard name'),
                'translatable' => true,
            ],
            'attributes' => [
                'disabled' => true,
            ]
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'select',
            'options' => [
                'label' => $this->getTranslator()->translate('Type'),
                'empty_option' => '',
                'value_options' => (new ServiceTypes($this->getTranslator()))->getTypes(),
            ],
            'attributes' => [
                'disabled' => true,
            ]
        ]);

        $this->add([
            'name' => 'helmet_type_priority',
            'type' => 'select',
            'options' => [
                'label' => $this->getTranslator()->translate('Order in shared service listings (HelMet)'),
                'value_options' => [
                    0 => $this->getTranslator()->translate('n/a'),
                    1 => $this->getTranslator()->translate('First'),
                    2 => $this->getTranslator()->translate('Second'),
                    3 => $this->getTranslator()->translate('Third'),
                    4 => $this->getTranslator()->translate('Fourth'),
                    5 => $this->getTranslator()->translate('Fifth'),
                    6 => $this->getTranslator()->translate('Sixth'),
                ]
            ],
            'attributes' => [
                'disabled' => true,
            ]
        ], ['priority' => -100]);

        $translations = $this->get('translations')->getContainer();
        $this->setOption('translations', $translations);

        foreach ($this as $element) {
            $element->setOption('translations', $translations);
        }
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'type',
                'required' => false,
            ],
            [
                'name' => 'name',
                'required' => false,
            ],
            [
                'name' => 'helmet_type_priority',
                'required' => false,
            ]
        ];
    }
}
