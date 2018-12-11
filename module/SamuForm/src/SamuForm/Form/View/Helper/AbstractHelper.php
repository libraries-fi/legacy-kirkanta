<?php

namespace SamuForm\Form\View\Helper;

use Exception;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\FormInterface;
use Zend\Form\View\Helper\AbstractHelper as ZendAbstractHelper;
use Samu\Stdlib\TreeIterator;

abstract class AbstractHelper extends ZendAbstractHelper implements HelperInterface
{
    protected $template = '';
    protected $tag = '';
    protected $options = [];
    protected $element;

    public function __invoke(ElementInterface $element = null, $options = null)
    {
        if (!func_num_args()) {
            return $this;
        }

        if ($element == null) {
            throw new Exception("Invalid element passed");
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }

        $this->element = $element;
        return $this->render($element);
    }

    public function providesLabel()
    {
        return false;
    }

    public function options(array $options)
    {
        $this->setOptions($options);
        return $this;
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'template':
                    $this->template = $value;
                    unset($options['template']);
                    break;

            }
        }

        $this->options = $options;
        return $this;
    }

    public function setDefaultOptions(array $options)
    {
        $this->options += $options;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getHtmlEscaper()
    {
        return $this->getView()->plugin('escape_html');
    }

    public function setTagName($name)
    {
        $this->tag = $name;
    }

    public function getTagName()
    {
        return $this->tag;
    }

    public function openTag(ElementInterface $element)
    {
        $attrstr = $this->createAttributesString($this->extractAttributes($element));

        if ($attrstr) {
            $attrstr = ' ' . $attrstr;
        }

        return sprintf('<%s%s>', $this->tag, $attrstr);
    }

    public function closeTag(ElementInterface $element)
    {
        return sprintf('</%s>', $this->tag);
    }

    public function getElementId(ElementInterface $element)
    {
        $id = $element->getAttribute('id');
        if (!$id) {
            $name = strtolower($element->getName());
            $id = preg_replace('/[^a-z0-9\-\[\]]+/', '-', $name);
            $id = preg_replace('/-{3,}+/', '--', $id);
            $id = trim($id, '-');
        }
        return $id;
    }

    protected function renderTemplate($partials, $template = null)
    {
        $template = is_null($template) ? $this->template : $template;
        foreach ($partials as $name => $markup) {
            $template = str_replace('&' . $name, $markup, $template);
        }
        return $template;
    }

    protected function extractAttributes(ElementInterface $element)
    {
        $id = $this->getElementId($element);

        if (!($element instanceof FormInterface)) {
            $id = 'input-' . $id;
        }

        $attrs = ['id' => $id] + $element->getAttributes();
//         $attrs = $element->getAttributes();
        if (!isset($attrs['class'])) {
            $attrs['class'] = '';
        }

        $id_class = $attrs['id'];

        if (!($element instanceof FieldsetInterface)) {
            $id_class = preg_replace('/\[\d+\]/', '', $id_class);
            $id_class = preg_replace('/\[--index--\]/', '', $id_class);
            $id_class = preg_replace('/-{3,}/', '--', $id_class);
        }
        $id_class = preg_replace('/\[([\w\-]+)\]/', '-$1', $id_class);

        $attrs['class'] .= ' ' . $id_class;

        return $attrs;
    }

    protected function getOptionsIterator()
    {
        return new TreeIterator($this->options);
    }
}
