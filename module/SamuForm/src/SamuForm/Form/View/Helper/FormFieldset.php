<?php

namespace SamuForm\Form\View\Helper;

use Exception;
use Zend\Form\Element;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputInterface;
use Zend\View\Model\ViewModel;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\RendererInterface;

class FormFieldset extends FormComplexInput
{
    protected $tag = 'fieldset';

    public static function create(HelperPluginManager $pm)
    {
        $sm = $pm->getServiceLocator();
        $renderer = $sm->get('ViewRenderer');
        return new static($renderer);
    }

    public function __construct(RendererInterface $renderer = null)
    {
        $this->renderer = $renderer;
    }

    protected function setInputFilter(InputFilterInterface $filter)
    {
        $this->setOption('input_filter', $filter);
    }

    protected function getInputFilter()
    {
        return $this->getOption('input_filter');
    }

    public function render(ElementInterface $fieldset)
    {
        if ($template = $fieldset->getOption('template')) {
            $model = new ViewModel;
            $model->setTemplate($template);
            $model->fieldset = $fieldset;
            $model->options = $this->getChildOptions($fieldset->getName());
            return $this->renderer->render($model);
        } else {
            return parent::render($fieldset);
        }
    }

    public function fields(FieldsetInterface $fieldset, array $fields)
    {
        $this->setInputFilter($fieldset->getInputFilter());

        $markup = '';
        foreach ($fields as $name) {
            $markup .= $this->renderChild($fieldset->get($name)) . PHP_EOL;
        }
        return $markup;
    }

    public function renderContent(ElementInterface $element)
    {
        $markup = '';
        $legend = $this->getTitleElementValue($element);

        if (!$legend) {
            $legend = $element->getLabel() ?: $element->getOption('label');
        }

        if (!($element instanceof FormInterface) and $legend) {
            $markup .= sprintf('<legend>%s</legend>', $legend);
        }

        $markup .= $this->renderChildren($element);
        return $markup;
    }

    public function providesLabel()
    {
        return true;
    }

    public function title(FieldsetInterface $fieldset)
    {
        return $this->getTitleElementValue($fieldset);
    }

    public function getFieldsetHelper()
    {
        return $this->getView()->plugin('samu_form_collection');
    }

    public function getRowHelper()
    {
        return $this->getView()->plugin('samu_form_row');
    }

    protected function renderChildren(ElementInterface $fieldset)
    {
        $markup = '';

        foreach ($fieldset as $element) {
            $markup .= $this->renderChild($element) . PHP_EOL;
        }

        return $markup;
    }

    public function child(ElementInterface $element, array $parent_options = [])
    {
        if ($parent_options) {
            $this->setOptions($parent_options);
        }
        return $this->renderChild($element);
    }

    protected function renderChild(ElementInterface $element)
    {
        $options = $this->getChildOptions($element->getName());
        $rowHelper = $this->getRowHelper();
        $markup = $rowHelper($element, $options);
        return $markup;
    }

    public function extractAttributes(ElementInterface $fieldset)
    {
        $a = parent::extractAttributes($fieldset);
        $class = str_replace('form-control', '', $a['class']);

        if ($fieldset->getOption('is_collection_row')) {
            $class .= ' form-collection-row ';
        }

        $a['class'] = $class;
        unset($a['name']);
        return $a;
    }

    protected function getChildOptions($name)
    {
        if (preg_match_all('/\[([\w\-]+)\]/', $name, $m)) {
            $name = end($m[1]);
        }

        $opts = $this->getOption('rows', []);
        $opts = isset($opts['children']) ? $opts['children'] : [];

        if ($this->getInputFilter() && $this->getInputFilter()->has($name)) {
            $input = $this->getInputFilter()->get($name);

            if ($input instanceof InputFilterInterface) {
                $opts[$name]['input_filter'] = $input;
            } else {
                $opts[$name]['required'] = $input->isRequired();
            }
        }
        $options = isset($opts[$name]) ? $opts[$name] : [];
        return $options + ['input_filter' => $this->getInputFilter()];
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
