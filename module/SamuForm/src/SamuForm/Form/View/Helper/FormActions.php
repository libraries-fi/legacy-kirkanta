<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\FormInterface;

class FormActions extends AbstractHelper
{
    protected $template = '
        <div class="form-actions">
            &actions
        </div>
    ';

    protected $tag = 'div';

    public function render(ElementInterface $form)
    {
        if (!$form instanceof FormInterface) {
            throw new \Exception("Passed element is not a form");
        }

        $partials = [
            'tag' => $this->tag,
            'actions' => $this->renderActions($form),
        ];

        return $this->renderTemplate($partials);
    }

    public function renderActions(FormInterface $form)
    {

        $elements = $form->getElements();
        $elementHelper = $this->getElementHelper();
        $markup = '';

        foreach ($elements as $element) {
            if ($element instanceof Element\Button) {
                $elementHelper->setOptions($this->getOptions() + $element->getOptions());
                $markup .= $elementHelper->render($element) . PHP_EOL;
            }
        }

        return $markup;
    }

    public function getElementHelper()
    {
        return $this->getView()->plugin('samu_form_element');
    }
}
