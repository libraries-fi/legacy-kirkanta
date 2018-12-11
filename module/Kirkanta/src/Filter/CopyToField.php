<?php

namespace Kirkanta\Filter;

use Zend\Filter\AbstractFilter;
use Zend\Form\FormInterface;

class CopyToField extends AbstractFilter
{

    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    public function filter($value)
    {
        if ($value !== null) {
            $filter = $this->getForm()->getInputFilter();
            $filter->get($this->getField())->setValue($value);
        }
    }

    public function setForm(FormInterface $form)
    {
        $this->options['form'] = $form;
    }

    public function getForm()
    {
        return $this->options['form'];
    }

    public function setField($field)
    {
        $this->options['field'] = $field;
    }

    public function getField()
    {
        return $this->options['field'];
    }
}
