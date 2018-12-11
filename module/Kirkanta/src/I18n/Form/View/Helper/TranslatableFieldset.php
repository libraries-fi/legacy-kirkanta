<?php

namespace Kirkanta\I18n\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Kirkanta\I18n\Form\TranslationFormMessages;
use Kirkanta\I18n\Form\Element\Translations;
use Kirkanta\I18n\Form\TranslatableFormInterface;
use SamuForm\Form\View\Helper\FormFieldset;

class TranslatableFieldset extends FormFieldset
{
    protected function prepare(FieldsetInterface $fieldset)
    {
        if ($fieldset->has('translations')) {
            $container = $fieldset->get('translations')->getContainer();
            $messages = new TranslationFormMessages($fieldset->get('translations')->getMessages());

            $locale = 'fi';

            $this->setOption('translations', $container);
            $this->setOption('tr_messages', $messages);
            $this->setOption('locale', $locale);
        }
    }
    public function render(ElementInterface $fieldset)
    {
        if ($fieldset instanceof FieldsetInterface) {
            $this->prepare($fieldset);
        }
        return parent::render($fieldset);
    }

    public function fields(FieldsetInterface $fieldset, array $fields)
    {
        if ($fieldset instanceof FieldsetInterface) {
            $this->prepare($fieldset);
        }
        return parent::fields($fieldset, $fields);
    }

    protected function getChildOptions($name)
    {
        $opts = parent::getChildOptions($name);
        if ($translations = $this->getOption('translations')) {
            $opts += [
                'translations' => $translations,
                'locale' => $this->getOption('locale'),
                'tr_messages' => $this->getOption('tr_messages'),
            ];
        }
        return $opts;
    }

    protected function renderChild(ElementInterface $element)
    {
        if ($element instanceof Translations) {
            return '';
        } else {
            return parent::renderChild($element);
        }
    }

    public function extractAttributes(ElementInterface $form)
    {
        $attrs = parent::extractAttributes($form);
        $attrs['class'] .= ' tr-fieldset';
        return $attrs;
    }
}
