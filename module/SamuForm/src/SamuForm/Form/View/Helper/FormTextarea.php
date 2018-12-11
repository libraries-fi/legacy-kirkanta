<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\FormInterface;

class FormTextarea extends FormComplexInput
{
    protected $tag = 'textarea';

    public function renderContent(ElementInterface $element)
    {
        $htmlEscaper = $this->getHtmlEscaper();
        return $htmlEscaper($element->getValue());
    }

    protected function extractAttributes(ElementInterface $element)
    {
        $attrs = parent::extractAttributes($element);
        if (empty($attrs['rows'])) {
            $attrs['rows'] = 4;
        }
        if (empty($attrs['cols'])) {
            $attrs['cols'] = 40;
        }
        return $attrs;
    }
}
