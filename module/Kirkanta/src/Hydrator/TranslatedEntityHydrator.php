<?php

namespace Kirkanta\Hydrator;

use Doctrine\Common\Persistence\ObjectManager;
use Kirkanta\Entity\TranslatableEntityInterface;

class TranslatedEntityHydrator extends ProperDoctrineObject
{
    protected $locale;

    public function __construct(ObjectManager $om, $locale = null, array $fields = [])
    {
        parent::__construct($om, $fields);
        $this->locale = $locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    protected function extractByValue($object)
    {
        $data = parent::extractByValue($object);
        $data = array_merge($data, $this->extractLocale($object, $this->locale));
        return $data;
    }

    protected function extractLocale($object, $locale)
    {
        if ($object instanceof TranslatableEntityInterface) {
            return $object->getTranslation($locale) ?: [];
        } else {
            return [];
        }
    }
}
