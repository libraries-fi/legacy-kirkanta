<?php

namespace Kirkanta\I18n\InputFilter;

use Zend\InputFilter\InputFilter;
// use Zend\InputFilter\InputFilterInterface;

/**
 * Implements support for forms with a Translations element.
 *
 * Extends a base InputFilter as a decorator.
 */
class TranslationsInputFilter extends InputFilter
{
    protected $field = 'translations';

    public function setFieldName($field)
    {
        $this->field = $field;
    }

    public function getFieldName()
    {
        return $this->field;
    }

    // public function isValid($context = null)
    // {
    //     $valid = parent::isValid($context);
    //     $inputs = $this->validationGroup ?: array_keys($this->inputs);
    //     $data = $this->getRawValues();
    //
    //     if (isset($data[$this->field])) {
    //         // $valid &= $this->validateTranslatedInputs($inputs, $data[$this->field], $context);
    //
    //         $valid &= $this->validateTranslated($inputs, $data[$this->field], $context);
    //     } else {
    //         // exit('not set');
    //     }
    //     return $valid;
    // }
    //
    // public function getMessages()
    // {
    //     $states = $this->translated_state;
    //     $messages = parent::getMessages();
    //     foreach ($states as $lang => $state) {
    //         foreach ($state[1] as $name => $failed) {
    //             $messages[$this->field][$lang][$name] = $failed->getMessages();
    //         }
    //     }
    //     return $messages;
    // }

    protected function validateTranslated(array $inputs, array $data, $context = null)
    {
        $clone = new $this;

        foreach ($this->inputs as $name => $input) {
            var_dump($name);
        }

        return true;
    }

    protected function validateTranslatedInputs(array $inputs, array $data, $context = null)
    {
        $tmp = [$this->validInputs, $this->invalidInputs, $this->data];
        $state = [];
        $valid = true;

        foreach ($data as $lang => $ldata) {
            $this->setData($ldata);
            $enabled = array_intersect($inputs, array_keys($ldata));
            $lvalid = $this->validateInputs($enabled, $ldata, $context);
            $state[$lang] = [$this->validInputs, $this->invalidInputs];
            // var_dump($lvalid . ': ' . print_r($enabled, true) . print_r($ldata, true));

            if ($this->isTranslationEmpty($ldata)) {
                $state[$lang] = [array_merge($state[$lang][0], $state[$lang][1]), []];
            } else {
                $valid &= $lvalid;
            }
        }
        list($this->validInputs, $this->invalidInputs) = $tmp;
        $this->translated_state = $state;
        $this->setData($tmp[2]);
        return $valid;
    }

    protected function isTranslationEmpty(array $data)
    {
        return count(array_filter($data)) == 0;
    }
}
