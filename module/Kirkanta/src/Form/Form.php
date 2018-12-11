<?php

namespace Kirkanta\Form;

use Zend\Form\Fieldset;
use Zend\Form\Form as BaseForm;
use Zend\Form\Element\Button;
use Zend\Form\Element\Collection;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class Form extends BaseForm implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    protected $form_id;

    const NO_BUTTONS        = 0;
    const SUBMIT_BUTTON     = 1;
    const RESET_BUTTON      = 2;
    const CANCEL_BUTTON     = 4;
    const DELETE_BUTTON     = 8;

    // public static function createInstance(ServiceLocatorInterface $services, array $options)
    // {
    //     return new static($services->get('MvcTranslator'), $options);
    // }

    public static function create(ServiceLocatorInterface $services, array $options)
    {
        return new static($services->get('MvcTranslator'), $options);
    }

    public function __construct(TranslatorInterface $translator, array $options = [])
    {
        parent::__construct($this->form_id, $options);
        $this->setTranslator($translator);
    }

    public function getStandardActions($buttons = self::SUBMIT_BUTTON)
    {
        $controls = array();

        if ($buttons & self::SUBMIT_BUTTON) {
            $controls['submit'] = [
                'name' => 'submit',
                'type' => 'button',
                'attributes' => [
                    'type' => 'submit',
                ],
                'options' => [
                    'label' => $this->tr('Submit'),
                ]
            ];
        }

        if ($buttons & self::CANCEL_BUTTON) {
            $controls['cancel'] = [
                'name' => 'cancel',
                'type' => 'button',
                'options' => [
                    'button_role' => 'link',
                    'label' => $this->tr('Cancel'),
                    'route' => $this->getUrlPrototype('list'),
                ],
                'attributes' => [
                    'type' => 'link',
                ],
            ];
        }

        if ($buttons & self::DELETE_BUTTON) {
            $controls['delete'] = [
                'name' => 'delete',
                'type' => 'button',
                'options' => [
                    'label' => $this->tr('Delete'),
                    'button_role' => 'link',
                    'route' => $this->getUrlPrototype('delete'),
                ],
                'attributes' => [
                    'type' => 'link',
                ],
            ];
        }

        return $controls;
    }

    public function setUrlBuilder($url_builder)
    {
        $this->url_builder = $url_builder;
    }

    public function getUrlBuilder()
    {
        return $this->url_builder;
    }

    protected function getUrlPrototype($name)
    {
        $proto = $this->url_builder->getUrlPrototype($name, [
            'id' => $this->getObject() ? $this->getObject()->getId() : null,
        ]);
        return $proto;
    }

    protected function tr($string)
    {
        return $this->getTranslator()->translate($string);
    }

    protected function prepareBindData(array $values, array $match)
    {
        /*
         * NOTE: Following code is a very ugly hack to fix another similar stupid hack in ZF2 core.
         *
         * Core hack causes collections that are not part of the submitted form data to be emptied
         * by Fieldset::bindValues(). While it would be better to fix that hack properly, I don't
         * care enough at this point.
         *
         * https://github.com/zendframework/zend-form/pull/19
         */

        if ($enabled = $this->getValidationGroup()) {
            foreach ($this->getFieldsets() as $name => $fieldset) {
                if (!in_array($name, $enabled)) {
                    $this->remove($name);
                }
            }
        }

        return parent::prepareBindData($values, $match);
    }
}
