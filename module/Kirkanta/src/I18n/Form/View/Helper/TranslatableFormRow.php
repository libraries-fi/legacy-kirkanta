<?php

namespace Kirkanta\I18n\Form\View\Helper;

use Kirkanta\I18n\Form\Element\Translations;
use Kirkanta\I18n\ContentLanguages;
use SamuForm\Form\View\Helper\FormRow;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\Element;

class TranslatableFormRow extends FormRow
{
    protected $locales;
    protected $defaultLocale = 'fi';

    public function getLocales()
    {
        if (!$this->locales) {
            $this->locales = (new ContentLanguages($this->getTranslator()))->getLanguages();
        }
        return $this->locales;
    }

    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    public function getActiveLocale()
    {
        return $this->getOption('locale', $this->defaultLocale);
    }

    public function render(ElementInterface $element)
    {
        if ($element instanceof Translations) {
            return;
        }

        if (!$element->getOption('translatable')) {
            return parent::render($element);
        }

        if ($element instanceof FieldsetInterface) {
            return parent::render($element);
        }

        if (!$this->getOption('translations')) {
            throw new \Exception(sprintf('No translations provided for element "%s".', $element->getName()));
        }

        $labelHelper = $this->getLabelHelper();
        $elementHelper = $this->getElementHelper()->getRenderer($element);
        $messagesHelper = $this->getMessagesHelper();
        $elementHelper->setOptions($this->getOptions() + $element->getOptions());

        $markup = '';

        foreach ($this->getLocales() as $locale => $language) {
            if ($locale == $this->getDefaultLocale()) {
                $trElement = $element;
            } else {
                $trElement = $this->createTranslationElement($element, $locale);
            }

            if ($elementHelper->providesLabel() or $trElement instanceof Element\Hidden) {
                $label = '';
            } else {
                if ($trElement->getLabel()) {
                    $escape = $this->getView()->plugin('escape_html');
                    $attrs = ['for' => $elementHelper->getElementId($trElement)];
                    $text = $escape($trElement->getLabel());
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

            $prefix = sprintf('<div class="input-group"> <span class="input-group-addon" title="%s">%s</span> ', $this->locales[$locale], $locale);
            $suffix = ' </div>';

            $partials = [
                'attributes' => $this->createAttributesString($this->extractAttributes($trElement, $locale)),
                'label' => $label,
                'info' => $info,
                'input' => $prefix . $elementHelper->render($trElement, $locale) . $suffix,
                'messages' => $messagesHelper->render($trElement, ['class' => 'errors']),
            ];

            $markup .= $this->renderTemplate($partials);
        }

        $classes = array_merge(['tr-group'], $this->getOption('locale') == 'all' ? ['show-all'] : []);
        $wrapPrefix = sprintf('<div class="%s">', implode(' ', $classes)) . PHP_EOL;
        $wrapSuffix = PHP_EOL . '</div>';

        return $wrapPrefix . $markup . $wrapSuffix;
    }

    protected function createTranslationElement(ElementInterface $element, $locale)
    {
        $parts = array_filter(preg_split('/[\[\]]+/', $element->getName()), function($val) { return strlen($val); });
        array_splice($parts, -1, 0, ['translations', $locale]);

        $name = rtrim(sprintf('%s[%s]', array_shift($parts), implode('][', $parts), '[]'));
        $copy = clone $element;
        $copy->setAttribute('name', $name);

        if ($translations = $this->getOption('translations')) {
            $messages = $this->getOption('tr_messages');
            $copy->setValue($translations->getTranslation(end($parts), $locale));
            $copy->setMessages($messages->get(end($parts), $locale) ?: []);
        }

        return $copy;
    }

    public function extractAttributes(ElementInterface $element, $locale = null)
    {
        $attrs = parent::extractAttributes($element);

        if ($element->getOption('translatable')) {
            $attrs['class'] .= ' tr-field tr-locale-' . $locale;
            if ($locale and $locale == $this->getActiveLocale()) {
                $attrs['class'] .= ' tr-active';
            }
        }

        return $attrs;
    }
}
