<?php

namespace KirkantaIndexing\Hydrator;

use DateTime;
use Exception;
use ReflectionClass;
use ReflectionProperty;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Proxy\Proxy as EntityProxy;
use Kirkanta\I18n\TranslatableInterface;
use Kirkanta\I18n\Translations;
use KirkantaIndexing\Annotation;
use Zend\Stdlib\Hydrator\AbstractHydrator;

class AnnotatedHydrator extends AbstractHydrator
{
    protected $em;
    protected $langs;

    /**
     * Requires EntityManagerInterface because the annotations rely on ORM module
     * instead of generic the ObjectManager.
     */
    public function __construct(EntityManagerInterface $em, array $langs)
    {
        parent::__construct();
        $this->em = $em;
        $this->langs = $langs;
    }

    public function __get($key) {
        if ($key == 'reader') {
            $this->reader = new CachedReader(new AnnotationReader, new ApcCache);
            return $this->reader;
        }
        return parent::__get($key);
    }

    public function getDocumentType($entity)
    {
        if ($entity instanceof EntityProxy) {
            $class = get_parent_class($entity);
        } else {
            $class = get_class($entity);
        }
        $refl = new ReflectionClass($class);
        $annotation = $this->reader->getClassAnnotation($refl, Annotation\Document::class);
        return $annotation ? $annotation->type : null;
    }

    public function isIndexable($entity)
    {
        return $this->getDocumentType($entity) != null;
    }

    public function extract($entity)
    {
        if (!is_object($entity)) {
            throw new Exception('Passed value is not an object');
        }

        try {
            $metadata = $this->em->getClassMetaData(get_class($entity));
        } catch (MappingException $e) {
            throw new Exception('Instance of ' . get_class($entity) . ' is not a valid Doctrine entity');
        }

        $translations = $entity instanceof TranslatableInterface ? $entity->getTranslations() : [];

        $properties = array_merge($metadata->getFieldNames(), $metadata->getAssociationNames());
        $reader = $this->reader;
        $values = [];
        // $translated = [];

        foreach ($properties as $prop) {
            $property = new ReflectionProperty($metadata->getName(), $prop);

            if (!$reader->getPropertyAnnotation($property, Annotation\Enabled::class)) {
                continue;
            }

            $property->setAccessible(true);
            $annotations = $reader->getPropertyAnnotations($property);
            $skip_default = false;

            foreach ($annotations as $ant) {
                switch (true) {
                    case $ant instanceof Annotation\Reference:
                        $method = $ant->extract;
                        $subfield = $ant->field;
                        $prop_alt = $ant->name ?: $prop;
                        $values[$prop_alt] = $this->extractReference($property->getValue($entity), $method, $subfield, $ant->translated);
                        $skip_default = true;

                        if ($ant->isList()) {
                            $values[$prop_alt] = array_values($values[$prop]);
                        }
                        break;

                    case $ant instanceof Annotation\Translated:
                        $values = $this->mergeTranslations($values, $translations, [$prop]);
                        $values[$prop]['fi'] = $property->getValue($entity);

                        if ($ant->fallback) {
                            $this->useDefaultForTranslated($values[$prop], $values[$prop]['fi']);
                        }
                        break;

                    case $ant instanceof Annotation\DateTime:
                        $values[$prop] = $this->formatDateTime($property->getValue($entity), $ant->format);
                        break;

                    /*
                     * NOTE: This annotation has to be placed AFTER Reference
                     * in entity definition or this will fail.
                     */
                    case $ant instanceof Annotation\Merge:
                        $skip_default = true;
                        $data = isset($values[$prop]) ? $values[$prop] : $property->getValue($entity);
                        unset($values[$prop]);
                        foreach ($data as $key => $value) {
                            if ($ant->fields and !in_array($key, $ant->fields, true)) {
                                continue;
                            }
                            if (isset($values[$key]) and is_array($values[$key]) and is_array($value)) {
                                $values[$key] = array_merge($value, $values[$key]);
                            } else {
                                $values[$key] = $value;
                            }
                        }
                        break;

                    /*
                     * NOTE: This annotation has to be placed AFTER Reference
                     * in entity definition because this hydrator does not
                     * sort annotations!
                     */
                    case $ant instanceof Annotation\Group:
                        $skip_default = true;
                        $data = isset($values[$prop]) ? $values[$prop] : $property->getValue($entity);
                        unset($values[$prop]);
                        $values[$ant->into][$prop] = $data;
                        break;
                }
            }

            if (!$skip_default and !array_key_exists($prop, $values)) {
                $method = 'get' . $this->propertyToMethod($prop);
                $values[$prop] = call_user_func([$entity, $method]);
            }
        }

        unset($values['translations']);
        return $values;
    }

    public function getAnnotationReader()
    {
        return $this->reader;
    }

    public function hydrate(array $data, $object)
    {
        throw new Exception('This hydrator is read-only');
    }

    /**
     * @param $reference Referenced object from which to extract
     * @param $method Method which to use (@see \KirkantaIndexing\Annotation\Reference::$extract)
     * @param $subfield When extracting as value, define from which field to extract
     */
    protected function extractReference($reference, $method, $subfield = null, $translated = false)
    {
        if (is_null($reference)) {
            return null;
        }

        if (!is_object($reference)) {
            throw new Exception(sprintf('Not a reference to an object (%s)', gettype($reference)));
        }

        switch ($method) {
            case 'field':
                // Need to use getter instead of reflection so that the proxy
                // object will be initialized.
                $method = 'get' . $this->propertyToMethod($subfield);

                if (method_exists($reference, $method)) {
                    $value = call_user_func([$reference, $method]);
                    if ($translated) {
                        $trdata = Translations::extractField($reference->getTranslations(), $subfield);
                        $trdata['fi'] = $value;
                        return $trdata;
                    }
                    return $value;
                } else {
                    throw new Exception(sprintf('Cannot fetch field %s (%s)', $subfield, get_class($reference)));
                }

            case 'raw':
                return $reference;

            case 'values':
                if ($reference instanceof PersistentCollection) {
                    $reference = iterator_to_array($reference);
                }
                if (is_array($reference)) {
                    return array_map([$this, 'extract'], $reference);
                }
                return $this->extract($reference);
        }
    }

    protected function propertyToMethod($name)
    {
        return preg_replace_callback('/_([a-z])/', function($m) {
            return strtoupper($m[1]);
        }, ucfirst($name));
    }

    protected function formatDateTime(DateTime $dt = null, $format)
    {
        if ($dt) {
            return $dt->format($format);
        }
    }

    protected function mergeTranslations(array $document, array $translations, array $fields)
    {
        foreach ($fields as $field) {
            $values = [];
            foreach ($this->langs as $lang) {
                $tr = isset($translations[$lang]) ? $translations[$lang] : [];
                $values[$lang] = isset($tr[$field]) ? $tr[$field] : null;
            }
            $document[$field] = $values;
        }
        return $document;
    }

    protected function useDefaultForTranslated(array &$data, $value)
    {
        foreach ($data as &$val) {
            if (!isset($val) || !strlen($val)) {
                $val = $value;
            }
        }
    }
}
