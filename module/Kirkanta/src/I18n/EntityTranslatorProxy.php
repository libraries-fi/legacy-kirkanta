<?php

namespace Kirkanta\I18n;

use Exception;
use Kirkanta\Entity\TranslatableEntity;

class EntityTranslatorProxy
{
    protected $entity;
    protected $locale;

    public function __construct($locale = null, TranslatableEntity $entity = null)
    {
        $this->entity = $entity;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function __call($method, $params)
    {
        if (substr($method, 0, 3) == 'get') {
            $prop = substr($method, 3);
            return $this->getProperty($prop);
        } elseif (substr($method, 0, 3) == 'set') {
            throw new Exception('Setting translated values not supported');
        } else {
            return call_user_func_array([$this->entity, $method], $params);
        }
    }

    protected function getProperty($prop)
    {
        $name = preg_replace('/([A-Z]+)/', '_$1', lcfirst($prop));
        $data = $this->entity->getTranslation($this->locale);

        if ($data) {
            return array_get($data, $name);
        }
    }
}
