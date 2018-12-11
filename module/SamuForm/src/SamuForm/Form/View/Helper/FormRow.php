<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\Element;

class FormRow extends AbstractHelper
{
    protected $template = '
        <div &attributes>
            &label
            &input
            &info
            &messages
        </div>
    ';

    public function render(ElementInterface $element)
    {
        if ($element instanceof FieldsetInterface) {
            $options = $this->getOptions() + $element->getOptions();

            if (empty($options['view'])) {
                $name = $element instanceof Element\Collection
                    ? 'samu_form_collection'
                    : 'samu_form_fieldset';

                $helper = $this->getView()->plugin($name);
            } else {
                $helper = $this->getView()->plugin($options['view']);
            }

            $helper->setOptions($options);
            return $helper->render($element);
        }

        $labelHelper = $this->getLabelHelper();
        $elementHelper = $this->getElementHelper()->getRenderer($element);
        $messagesHelper = $this->getMessagesHelper();
        $elementHelper->setOptions($this->getOptions() + $element->getOptions());

        if ($element->getAttribute('type') == 'hidden') {
            return $elementHelper->render($element);
        }

        if ($elementHelper->providesLabel()) {
            $label = '';
        } else {
            if ($element->getLabel()) {
                $escape = $this->getView()->plugin('escape_html');
                $attrs = ['for' => 'input-' . $elementHelper->getElementId($element)];
                $text = $escape($element->getLabel());
                $label = $labelHelper->openTag($attrs) . $text . $labelHelper->closeTag();
            } else {
                $label = '';
            }
        }

        if ($element->getOption('info')) {
            $info = $this->getInfoTextHelper()->render($element);
        } else {
            $info = '';
        }

        $partials = [
            'attributes' => $this->createAttributesString($this->extractAttributes($element)),
            'label' => $label,
            'info' => $info,
            'input' => $elementHelper->render($element),
            'messages' => $messagesHelper->render($element, ['class' => 'errors']),
        ];
        return $this->renderTemplate($partials);
    }

    public function getElementHelper()
    {
        return $this->getView()->plugin('samu_form_element');
    }

    public function getLabelHelper()
    {
        return $this->getView()->plugin('form_label');
    }

    public function getMessagesHelper()
    {
        return $this->getView()->plugin('form_element_errors');
    }

    public function extractAttributes(ElementInterface $element)
    {
        $attrs['class'] = (empty($attrs['class']) ? '' : $attrs['class'] . ' ') . 'form-group';
        if (count($element->getMessages())) {
            $attrs['class'] .= ' error';
        }
        $id = $this->getElementId($element);
        $id = 'field-' . trim($id, '-');
        $attrs['id'] = $id;

        $id_class = $attrs['id'];

        if (!($element instanceof FieldsetInterface)) {
            $id_class = preg_replace('/\[\d+\]/', '', $id_class);
            $id_class = preg_replace('/\[--index--\]/', '', $id_class);
            $id_class = preg_replace('/-{3,}/', '--', $id_class);
        }
        $id_class = preg_replace('/\[(\w+)\]/', '-$1', $id_class);

        $attrs['class'] .= ' ' . $id_class;

        if ($this->getOption('required')) {
            $attrs['class'] .= ' form-required';
        }

        return $attrs;
    }

    public function getInfoTextHelper()
    {
        return $this->getView()->plugin('samu_form_info_text');
    }

}
