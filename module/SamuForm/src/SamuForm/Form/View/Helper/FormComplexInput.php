<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;

abstract class FormComplexInput extends AbstractHelper
{
    protected $template = '<&tag &attributes>&content</&tag>';

    protected $validTagAttributes = [
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

        'rows'           => true,
        'cols'           => true,
    ];

    abstract public function renderContent(ElementInterface $element);

    public function render(ElementInterface $element)
    {
        $this->element = $element;

        $partials = [
            'tag' => $this->getTagName(),
            'attributes' => $this->createAttributesString($this->extractAttributes($element)),
            'content' => $this->renderContent($element),
        ];

        return $this->renderTemplate($partials);
    }

    protected function extractAttributes(ElementInterface $element)
    {
        $attrs = parent::extractAttributes($element);
        $attrs['class'] .= ' form-control';
        return $attrs;
    }
}
