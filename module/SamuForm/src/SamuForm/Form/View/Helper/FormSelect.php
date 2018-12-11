<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;

class FormSelect extends FormComplexInput
{
    protected $tag = 'select';

    public function render(ElementInterface $element)
    {
        $markup = parent::render($element);

        if ($element->useHiddenElement()) {
            $hidden = sprintf('<input type="hidden" name="%s" value=""/>', $element->getName());
            $markup = implode(PHP_EOL, [$hidden, $markup]);
        }

        return $markup;
    }

    /*
     * TODO: add support for optgroups!
     */
    public function renderContent(ElementInterface $element)
    {
        $options = $element->getOptions();

        if (!count($element->getValueOptions())) {
            return '';
        }

        $valueOptions = $element->getValueOptions();

        $htmlEscaper = $this->getHtmlEscaper();
        $markup = '';
        $elementValue = $element->getValue();

        if (!is_array($elementValue)) {
            $elementValue = array($elementValue);
        }

        $elementValue = array_map('strval', $elementValue);

        if (!is_null($element->getEmptyOption())) {
            $valueOptions = [null
            => $element->getEmptyOption()] + $valueOptions;
        }

        foreach ($valueOptions as $value => $options) {
            if (!is_array($options)) {
                $label = $options;
            } else {
                $label = isset($options['label']) ? $options['label'] : '';
                $value = isset($options['value']) ? $options['value'] : $value;
            }

            $selected = '';

            /*
             * NOTE: Works because elements still seem to contain string values after filtering!
             */
            if (in_array((string)$value, $elementValue, true)) {
                $selected = ' selected="selected"';
            }

            $value = $htmlEscaper($value);
            $label = $htmlEscaper($label);

            if (is_null($value)) {
                $markup .= sprintf('<option%s>%s</option>', $selected, $label);
            } else {
                $markup .= sprintf('<option value="%s"%s>%s</option>', $value, $selected, $label);
            }
        }

        return $markup;
    }

    public function extractAttributes(ElementInterface $element)
    {
        $attrs = parent::extractAttributes($element);

        if ($element->getAttribute('multiple')) {
            $attrs['name'] .= '[]';
        }
        return $attrs;
    }
}
