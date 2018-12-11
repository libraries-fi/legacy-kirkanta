<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\FormInterface;

class FormElement extends AbstractHelper
{
    public function render(ElementInterface $element)
    {
        if ($helper = $this->getRenderer($element)) {
            $helper->setOptions($this->getOptions());
            return $helper($element);
        } else {
            return get_class($element);
        }
    }

    public function getRenderer(ElementInterface $element)
    {
        $options = $element->getOptions() + $this->getOptions();

        if (!empty($options['view'])) {
            return $this->getView()->plugin($options['view']);
        }

        if ($element instanceof FieldsetInterface) {
            return $this->getView()->plugin('samu_form_collection');
        }

        $classmap = [
            // Radio inherits Checkbox so make it first
            'Radio'         => 'samu_form_radio',
            'Checkbox'      => 'samu_form_checkbox',

            'Collection'    => 'samu_form_collection',
            'Fieldset'      => 'samu_form_fieldset',

            'Button'        => 'samu_form_button',
            'Email'         => 'samu_form_input',
            'File'          => 'samu_form_file',
            'Number'        => 'samu_form_input',
            'Hidden'        => 'samu_form_input',
            'Password'      => 'samu_form_input',
            'Select'        => 'samu_form_select',
            'Text'          => 'samu_form_input',
            'Textarea'      => 'samu_form_textarea',

            'SamuForm\Form\Element\ArrayCollection' => 'samu_form_collection',
        ];

        foreach ($classmap as $class => $helperName) {
            if (strpos($class, '\\') === false) {
                $class = 'Zend\\Form\\Element\\' . $class;
            }
            if ($element instanceof $class) {
                return $this->getView()->plugin($helperName);
            }
        }

        if (in_array($element->getAttribute('type'), ['button', 'reset', 'submit'])) {
            return $this->getView()->plugin('samu_form_button');
        }

        $this->setOptions([]);
        return $this->getView()->plugin('samu_form_input');
    }
}
