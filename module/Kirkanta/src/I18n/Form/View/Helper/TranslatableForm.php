<?php

namespace Kirkanta\I18n\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Kirkanta\I18n\Form\Element\Translations;
use Kirkanta\I18n\Form\TranslatableFormInterface;
use SamuForm\Form\View\Helper\Form;

class TranslatableForm extends Form
{
    public function render(ElementInterface $form)
    {
        if ($form instanceof TranslatableFormInterface) {
            $this->setOption('translations', $form->getTranslationsContainer());
            $this->setOption('tr_messages', $form->getTranslationMessages());
            $this->setOption('locale', $form->getActiveLocale());
        }
        return parent::render($form);
    }

    public function fields(FieldsetInterface $form, array $fields)
    {
        if ($form instanceof TranslatableFormInterface) {
            $this->setOption('translations', $form->getTranslationsContainer());
            $this->setOption('tr_messages', $form->getTranslationMessages());
            $this->setOption('locale', $form->getActiveLocale());
        }
        return parent::fields($form, $fields);
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
        $attrs['class'] .= ' tr-form';
        return $attrs;
    }
}
