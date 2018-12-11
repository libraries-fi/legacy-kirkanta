<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;

class FormCheckbox extends FormInput
{
    public function render(ElementInterface $element, $withAttrs = true)
    {
        $html = parent::render($element, $withAttrs);

        if ($element->useHiddenElement() && $this->getOption('fallback', true)) {
            $html = $this->renderFallback($element) . $html;
        }

        if ($this->getOption('with_label', false)) {
            $attrs = $this->extractAttributes($element);
            $label = $element->getOption('label');
            $html = sprintf('<label for="%s">%s %s</label>', $attrs['id'], $label, $html);
        }
        return $html;
    }

    public function renderFallback(ElementInterface $element)
    {
        $name = $element->getName();
        $value = $element->getUncheckedValue();
        $template = '<input type="hidden" name="&input-name" value="&default-value"/> ';
        return $this->renderTemplate(['input-name' => $name, 'default-value' => $value], $template);
    }

    public function extractAttributes(ElementInterface $element)
    {
        $attrs = parent::extractAttributes($element);
        $attrs['value'] = $element->getCheckedValue();

        if ($element->isChecked()) {
            $attrs['checked'] = 'checked';
        }

        return $attrs;
    }
}
