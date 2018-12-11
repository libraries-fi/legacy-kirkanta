<?php

namespace SamuForm\Form\View\Helper;

use Exception;
use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\InputFilter\CollectionInputFilter;

class FormCollection extends FormFieldset
{
    public function render(ElementInterface $element)
    {
        $this->markCollectionRows($element);
        return parent::render($element);
    }

    public function extractAttributes(ElementInterface $fieldset)
    {
        $attrs = parent::extractAttributes($fieldset);
        $attrs['class'] .= ' form-collection ';

        if ($fieldset->getOption('allow_add') || $fieldset->getOption('allow_remove')) {
            $attrs['class'] .= ' dynamic-collection ';
        }
        return $attrs;
    }

    public function renderTemplateItem(ElementInterface $collection)
    {
        if (!method_exists($collection, 'getTemplateElement')) {
            throw new Exception('Invalid element passed');
        }

        $elementHelper          = $this->getRowHelper();
        $escapeHtmlAttribHelper = $this->getHtmlEscaper();
        $templateMarkup         = '';
        $elementOrFieldset = $collection->getTemplateElement();
        $this->markCollectionRows([$elementOrFieldset]);

        $templateMarkup .= $elementHelper($elementOrFieldset);

        return sprintf(
            '<span class="template" data-template="%s"></span>',
            $escapeHtmlAttribHelper($templateMarkup)
        );
    }

    protected function renderGhost(ElementInterface $fieldset)
    {
        $markup = sprintf('<input type="hidden" name="%s"/>', $fieldset->getName());
        return $markup;
    }

    protected function renderChildren(ElementInterface $fieldset)
    {
        $markup = parent::renderChildren($fieldset);

        if ($fieldset->getOption('should_create_template')) {
            $markup = $this->renderTemplateItem($fieldset) . $markup;

            if ($fieldset->getOption('allow_remove')) {
//                 $markup .= $this->renderGhost($fieldset);
            }
        }

        return $markup;
    }

    protected function getChildOptions($name)
    {
        if (preg_match('/^[\w\-]+\[([\w\-]+)\]/', $name, $m)) {
            $name = $m[1];
        }

        $input_filter = $this->getInputFilter();

        // Having to do this might be a bug in the app code using this module, but we'll sort
        // it out later.
        if ($input_filter instanceof CollectionInputFilter) {
            $input_filter = $input_filter->getInputFilter();
        }

        $opts = $this->getOption('rows', []);
        $opts = isset($opts['children']) ? $opts['children'] : [];
        $opts[$name]['input_filter'] = $input_filter;
        return isset($opts[$name]) ? $opts[$name] : [];
    }

    protected function markCollectionRows($rows)
    {
        foreach ($rows as $element) {
            $element->setOption('is_collection_row', true);
        }
    }

    protected function getTitleElementValue(ElementInterface $fieldset)
    {
        $fields = $this->getOptionsIterator()->rows->title_element->value('array');
        $title = [];

        if (!$fields) {
            $fields = $this->getOptionsIterator()->title_element->value('array');
        }

        try {
            if ($fields) {
                foreach ($fields as $field) {
                    $title[] = $fieldset->get($field)->getValue();
                }
            }
            return implode(' ', $title);
        } catch (Exception $e) {
            return null;
        }
    }
}
