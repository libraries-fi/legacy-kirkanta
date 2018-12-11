<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;

class FormButton extends FormComplexInput
{
    protected $tag = 'button';

    public function providesLabel()
    {
        return true;
    }

    public function renderContent(ElementInterface $element)
    {
        return $element->getLabel();
    }

    public function extractAttributes(ElementInterface $element) {
        $attrs = parent::extractAttributes($element);
        $attrs['class'] = 'btn ';
        if ($role = $element->getOption('button_role')) {
            $attrs['class'] .= 'btn-' . $role;
        } else {
            $attrs['class'] .= 'btn-primary';
        }

        if ($this->isLinkButton($element)) {
            $this->validTagAttributes['href'] = true;

            unset($attrs['type']);
            unset($attrs['name']);

            if ($route = $element->getOption('route')) {
                $url_plugin = $this->getView()->plugin('url');
                $attrs['href'] = $url_plugin($route['route'], $route['params']);
            }
        }

        return $attrs;
    }

    public function getTagName()
    {
        return $this->isLinkButton($this->element) ? 'a' : 'button';
    }

    public function isLinkButton(ElementInterface $element)
    {
        return $element->getAttribute('type') == 'link';
    }
}
