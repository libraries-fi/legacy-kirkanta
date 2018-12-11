<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;

class FormRadio extends FormComplexInput
{
    protected $template = '
        <label class="nested-control">
            <span>&label</span>
            <input type="radio" name="&name" value="&value" &checked/>
        </label>
    ';

    public function providesLabel()
    {
        return true;
    }

    public function render(ElementInterface $element)
    {
        $htmlEscaper = $this->getHtmlEscaper();
        $options = $element->getValueOptions();
        $markup = '<span class="control-label">&label</span>';
        $label = $htmlEscaper($element->getLabel());
        $markup = $this->renderTemplate(['label' => $label], $markup);

        foreach ($options as $value => $label) {
            $markup .= $this->renderTemplate([
                'label' => $htmlEscaper($label),
                'value' => $value,
                'name' => $element->getName(),
                'checked' => $this->isChecked($element, $value) ? 'checked' : '',
            ]);
        }
        return $markup;
    }

    public function renderContent(ElementInterface $element)
    {
        return $element->getLabel();
    }

    public function isChecked($element, $value)
    {
        return (string)$element->getValue() === (string)$value;
    }
}
