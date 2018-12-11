<?php

namespace Kirkanta\Form\Element;

use ArrayObject;
use Kirkanta\Form\Fieldset\CustomDataFieldset;
use Kirkanta\Hydrator\ArrayObjectHydrator;
use Zend\Form\Element\Collection;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;

class CustomDataCollection extends Collection implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        $this->setHydrator(new ArrayObjectHydrator);
        $this->setOptions([
            'use_hydrator' => false,
            'label' => $this->getTranslator()->translate('Values'),
            'template' => 'kirkanta/partial/custom-data-collection.phtml',
            'count' => 0,
            'should_create_template' => true,
            'target_element' => ['type' => CustomDataFieldset::class],
            'template_placeholder' => '--index--',
        ]);
    }

    public function setObject($data)
    {
        if (is_array($data)) {
            $data = array_map(function($row) { return new ArrayObject($row); }, $data);
            $data = new ArrayObject($data);
        }
        parent::setObject($data);
    }
}
