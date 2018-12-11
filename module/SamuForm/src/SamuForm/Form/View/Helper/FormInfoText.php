<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;

class FormInfoText extends AbstractHelper
{
    protected $template = '<&tag class="form-field-info">&text</&tag>';
    protected $tag = 'p';

    public function render(ElementInterface $element, $withAttrs = true)
    {
        $partials = [
            'tag' => $this->tag,
            'text' => $element->getOption('info'),
        ];
        return $this->renderTemplate($partials);
    }
}
