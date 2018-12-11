<?php

namespace Kirkanta\Form;

use Zend\Form\Fieldset;
use Zend\Form\FieldsetInterface;
use Zend\InputFilter\InputFilter;

class TemplateSelectForm extends Form
{
    protected $form_id = 'template-select';

    public function init()
    {
        parent::init();

        $this->setUrlBuilder($this->getOption('url_builder', null));
        $this->templates = [];
        $this->setLabel($this->tr('Choose templates'));
        $this->setAttribute('class', 'template-select-form');

        $this->add([
            'name' => 'name_filter',
            'options' => [
                'label' => $this->tr('Filter'),
                'label_attributes' => [
                    'class' => 'sr-only',
                ],
            ],
            'attributes' => [
                'placeholder' => $this->tr('Search for items by name...'),
            ],
        ]);

        $this->setTemplateEntities($this->getOption('templates') ?: []);

        $buttons = self::SUBMIT_BUTTON | self::CANCEL_BUTTON;
        array_map([$this, 'add'], $this->getStandardActions($buttons));
    }

    public function setTemplateEntities(array $templates)
    {
        $fieldset = new Fieldset('templates');
        $this->addTemplateEntities($fieldset, $templates);
        $this->add($fieldset);
    }

    public function addTemplateEntities(FieldsetInterface $fieldset, array $items)
    {
        if ($this->getInputFilter()->has($fieldset->getName())) {
            $filter = $this->getInputFilter()->get($fieldset->getName());
        } else {
            $filter = new InputFilter($fieldset->getName());
            $this->getInputFilter()->add($filter);
        }

        foreach ($items as $item) {
            $this->templates[$item->getId()] = $item;
            $fieldset->add([
                'name' => sprintf('item_%d', $item->getId()),
                'type' => 'checkbox',
                'required' => true,
                'options' => [
                    'label' => $item->getLabel(),
                    'checked_value' => $item->getId(),
                    'template_input' => true,
                    'use_hidden_element' => false,
                ]
            ]);
            $filter->add([
                'name' => sprintf('item_%d', $item->getId()),
                'required' => false,
            ]);
        }

    }

    public function getSelectedTemplates()
    {
        return $this->getSelectedValues($this->get('templates'));
    }

    public function getSelectedValues(FieldsetInterface $fieldset)
    {
        $items = [];
        foreach ($fieldset->getElements() as $element) {
            if ($element->getOption('template_input') and $element->isChecked()) {
                $items[] = $this->templates[$element->getValue()];
            }
        }
        return $items;
    }

    public function getInputFilterSpecification()
    {
        return [];

        $filter = [];
        foreach ($this->templates as $i => $item) {
            $filter[] = [
                'name' => sprintf('item_%d', $i),
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                ],
            ];
        }
        return $filter;
    }
}
