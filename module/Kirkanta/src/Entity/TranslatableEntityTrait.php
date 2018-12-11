<?php

namespace Kirkanta\Entity;

use BadMethodCallException;
use Doctrine\ORM\Mapping as ORM;
use Kirkanta\I18n\TranslatableTrait;
use Kirkanta\I18n\Translations;

trait TranslatableEntityTrait
{
    use TranslatableTrait;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations(array $data)
    {
        $this->translations = $data;
    }

    public function hasTranslations($lang)
    {
        return !empty($this->translations[$lang]);
    }

    public function translateField($field, array $data)
    {
        foreach ($data as $lang => $value) {
            $this->translations[$lang][$field] = $value;
        }
    }

    public function getTranslatedValue($lang, $field)
    {
        if ($lang == Translations::defaultLanguage()) {
            return call_user_func([$this, $this->getterForField($field)]);
        }
        return isset($this->translations[$lang][$field])
            ? $this->translations[$lang][$field]
            : null;
    }

    public function setTranslatedValue($lang, $field, $value)
    {
        $this->translations[$lang][$field] = $value;
    }

    public function getTranslatedValues($field)
    {
        $values = [];
        $values['fi'] = call_user_func([$this, $this->getterForField($field)]);

        foreach ($this->getTranslations() as $lang => $data) {
            $values[$lang] = array_get($data, $field);
        }

        return $values;
    }

    protected function getterForField($field)
    {
        $getter = 'get' . implode('', array_map('ucfirst', explode('_', $field)));

        if (!method_exists($this, $getter)) {
            throw new BadMethodCallException(sprintf('Method \'%s\' does not exist for entity \'%s\'', $getter, get_class($this)));
        }

        return $getter;
    }
}
