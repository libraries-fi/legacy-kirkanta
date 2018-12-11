<?php

namespace Kirkanta\Hydrator;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\PersistentCollection;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByValue;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Hydrator\Strategy\StrategyInterface;

use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;

/**
 * Expands the original hydrator.
 *
 * Allows one to define what fields to extract, this is a useful optimization with entities that
 * have lots of associations that need not be loaded most of the time.
 */
class ProperDoctrineObject extends DoctrineObject
{
    /*
     * Allows to define a set of fields that will only be extracted; others will
     * therefore be ignored. This is a neat optimization for use with table
     * generators etc.
     */
    protected $fields;

    public function __construct(ObjectManager $om, array $fields = [])
    {
        parent::__construct($om, true);
        $this->fields = $fields;
        // $this->setNamingStrategy(new UnderscoreNamingStrategy);
        $this->setNamingStrategy(new NamingStrategy\AlwaysUnderscore);
    }

    protected function extractByValue($object)
    {
        if (!$this->fields) {
            return (new ClassMethods)->extract($object);
        }

        foreach ($this->fields as $field) {
            $getter = 'get' . implode('', array_map('ucfirst', explode('_', $field)));
            $value = $this->extractValue($field, $object->$getter());
            $data[$field] = $value;
        }
        return $data;
    }
   protected function prepareStrategies()
   {
        parent::prepareStrategies();

        $filter = new UnderscoreNamingStrategy;

        foreach ($this->strategies as $field => $strategy) {
            if ($strategy instanceof AllowRemoveByValue) {
                // var_dump($strategy->getCollectionName());
                // var_dump($filter->hydrate($strategy->getCollectionName()));
                $strategy->setCollectionName($filter->hydrate($strategy->getCollectionName()));
            }
        }
    }
}
