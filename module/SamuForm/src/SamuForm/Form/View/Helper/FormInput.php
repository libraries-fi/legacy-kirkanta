<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;

class FormInput extends AbstractHelper
{
    protected $template = '<&tag &attributes/>';
    protected $tag = 'input';

    protected $validTagAttributes = array(
        'name'           => true,
        'accept'         => true,
        'alt'            => true,
        'autocomplete'   => true,
        'autofocus'      => true,
        'checked'        => true,
        'dirname'        => true,
        'disabled'       => true,
        'form'           => true,
        'formaction'     => true,
        'formenctype'    => true,
        'formmethod'     => true,
        'formnovalidate' => true,
        'formtarget'     => true,
        'list'           => true,
        'max'            => true,
        'maxlength'      => true,
        'min'            => true,
        'multiple'       => true,
        'pattern'        => true,
        'placeholder'    => true,
        'readonly'       => true,
        'required'       => true,
        'size'           => true,
        'src'            => true,
        'step'           => true,
        'type'           => true,
        'value'          => true,
    );

    public function render(ElementInterface $element, $withAttrs = true)
    {
        $partials = [
            'tag' => $this->tag,
            'attributes' => $this->createAttributesString($this->extractAttributes($element)),
        ];

        return $this->renderTemplate($partials);
    }

    protected function extractAttributes(ElementInterface $element)
    {
        $a = parent::extractAttributes($element);

        if (empty($a['type'])) {
            $a['type'] = 'text';
        }

        if (!in_array($a['type'], ['radio', 'checkbox'])) {
            $a['class'] .= ' form-control';
        }

        if (empty($a['value']) && $element->getValue()) {
            $a['value'] = $element->getValue();
        }

        return $a;
    }
}
