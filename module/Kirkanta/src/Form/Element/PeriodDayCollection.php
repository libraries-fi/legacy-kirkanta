<?php

namespace Kirkanta\Form\Element;

use ArrayObject;
use Kirkanta\Form\Fieldset\PeriodDayFieldset;
use Kirkanta\Hydrator\ArrayObjectHydrator;
use Zend\Form\Element\Collection;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\InputFilterProviderInterface;

class PeriodDayCollection extends Collection implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function init()
    {
        $this->setHydrator(new ArrayObjectHydrator);
        $this->setOptions([
            'use_hydrator' => false,
            'label' => $this->getTranslator()->translate('Days'),
            'template' => 'kirkanta/partial/period-days.phtml',
            'should_create_template' => true,
            'target_element' => ['type' => PeriodDayFieldset::class],
            'template_placeholder' => '--index--',
        ]);
    }

    public function setObject($data)
    {
        if (is_array($data) and is_array(current($data))) {
            foreach ($data as $i => $row) {
                $data[$i] = new ArrayObject($row);
                if (!empty($data[$i]['times'])) {
                    foreach ($data[$i]['times'] as $j => $times) {
                        $data[$i]['times'][$j] = new ArrayObject($times);
                    }
                }
            }
        }
        parent::setObject($data);
    }
}
