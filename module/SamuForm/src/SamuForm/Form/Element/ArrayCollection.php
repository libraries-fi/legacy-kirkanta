<?php

namespace SamuForm\Form\Element;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Element;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\Factory;
use Zend\Form\Fieldset;
use Zend\Form\FormFactoryAwareInterface;
use Zend\Form\FormFactoryAwareTrait;
use Zend\Form\FormInterface;
use Zend\Stdlib\ArrayUtils;

class ArrayCollection extends Element implements ElementPrepareAwareInterface, FormFactoryAwareInterface, InputFilterProviderInterface, IteratorAggregate
{
    use FormFactoryAwareTrait;

    protected $rows = [];
    protected $template;

    public function getIterator()
    {
        if (!$this->rows) {
            $this->initCollection();
        }
        return new ArrayIterator($this->rows);
    }

    public function shouldCreateTemplate()
    {
        return $this->getOption('should_create_template');
    }

    public function getTemplateElement()
    {
        if (!$this->template and $this->shouldCreateTemplate()) {
            $this->template = $this->createRow('--index--');
        }
        return $this->template;
    }

    public function prepareElement(FormInterface $form)
    {
        $name = $this->getName();
        $elements = iterator_to_array($this);

        if ($template = $this->getTemplateElement()) {
            $elements[] = $template;
        }

        foreach ($elements as $element) {
            $element->setName(sprintf('%s[%s]', $name, $element->getName()));

            if ($element instanceof ElementPrepareAwareInterface) {
                $element->prepareElement($form);
            }
        }
    }

    protected function initCollection()
    {
        if (!is_array($this->getValue())) {
            return;
        }

        foreach ($this->getValue() as $i => $row) {
            $element = $this->createRow($i);
            $element->populateValues($row);
            $this->rows[$i] = $element;
        }

//         var_dump(count($this->rows));
    }

    protected function createRow($i)
    {
        $target = $this->getOption('target_element');

        if (is_string($target)) {
            return new $target;
        }

        if (!is_array($target)) {
            throw new Exception('Invalid target_element');
        }

        $fieldset = new Fieldset;
        $fieldset->setName($i);

        foreach ($target as $name => $defs) {
            $defs['name'] = $name;
            $element = $this->getFormFactory()->create($defs);
            $fieldset->add($element);
            $fieldset->setOption('is_collection_row', true);
        }

        return $fieldset;
    }

    /**
     * This method NOT provided by the trait NOR defined by the interface...
     */
    public function getFormFactory()
    {
        if (!$this->factory) {
            $this->factory = new Factory;
        }
        return $this->factory;
    }

    public function getInputFilterSpecification()
    {
        return [];
    }


}
