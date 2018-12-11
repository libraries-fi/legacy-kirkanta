<?php

namespace SamuForm\Form\View\Helper;

use Exception;
use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;

class Form extends FormFieldset
{
    protected $tag = 'form';

    protected $validTagAttributes = array(
        'class' => true,
        'action' => true,
        'method' => true,
        'enctype' => true,
    );

    public function render(ElementInterface $form)
    {
        $form->prepare();
        $this->setInputFilter($form->getInputFilter());
        return parent::render($form);
    }

    public function openTag(ElementInterface $form)
    {
        $form->prepare();
        $this->setInputFilter($form->getInputFilter());
        return parent::openTag($form);
    }

    public function fields(FieldsetInterface $form, array $fields)
    {
        $this->setInputFilter($form->getInputFilter());
        return parent::fields($form, $fields);
    }

    protected function renderChildren(ElementInterface $form)
    {
        if (!$form instanceof FieldsetInterface) {
            throw new Exception('Passed element is not a fieldset');
        }

        $enabled = array_flip($form->getValidationGroup() ?: []);
        // $markup = parent::renderChildren($form);
        $markup = '';
        $actions = [];

        foreach ($form as $id => $element) {
            if ($element instanceof Element\Button) {
                $actions[] = $element;
            } else if (!empty($enabled) && !isset($enabled[$id])) {
                continue;
            } else {
                // var_dump('render ' . $id);
                $markup .= $this->renderChild($element) . PHP_EOL;
            }
        }

        if (!empty($actions)) {
            $actionsHelper = $this->getActionsHelper();
            $markup .= $actionsHelper->render($form);
        }

        if ($label = $form->getLabel()) {
            $escaper = $this->getHtmlEscaper();
            $label = $escaper($label);
            $markup = sprintf('<h2 class="form-title">%s</h2>', $label) . $markup;
        }

        return $markup;
    }

    protected function renderChild(ElementInterface $element)
    {
        if (!$element instanceof Element\Button) {
            return parent::renderChild($element);
        }
    }

    public function getElementHelper()
    {
        return $this->getView()->plugin('samu_form_element');
    }

    public function getActionsHelper()
    {
        return $this->getView()->plugin('samu_form_actions');
    }

    public function extractAttributes(ElementInterface $form)
    {
        $attrs = parent::extractAttributes($form);
        if (!isset($attrs['id'])) {
            $attrs['id'] = $form->getName();
        }
        return $attrs;
    }
}
