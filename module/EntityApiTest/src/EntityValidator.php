<?php

namespace KirkantaTest\EntityApi;

use ReflectionClass;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Validator that checks that every entity class has required getters and setters.
 */
class EntityValidator
{
    private $entities;

    public static function create(ServiceLocatorInterface $services)
    {
        return new static($services->get(EntityManager::class));
    }

    public function __construct(EntityManagerInterface $entities)
    {
        $this->entities = $entities;
        $this->ignore = [
            'setCreated',
            'getLabelKey',
            'setLabelKey',

            'Kirkanta\Entity\AccessibilityFeature' => [
                'setReferences',
            ],
            'Kirkanta\Entity\AccessibilityReference' => [
                'getSourceField',
                'setSourceField',
            ],
            'Kirkanta\Entity\City' => [
                'setOrganisations',
            ],
            'Kirkanta\Entity\Consortium' => [
                'setCities',
                'setOrganisations',
            ],
            'Kirkanta\Entity\Organisation' => [
                'setChildren',
                'setMobileStops',
                'getForceNoConsortium',
                'setForceNoConsortium',
            ],
            'Kirkanta\Entity\Region' => [
                'setCities',
            ],
            'Kirkanta\Entity\Role' => [
                'setChildren',
            ],
            'Kirkanta\Entity\ServiceType' => [
                'setServices',
            ],
        ];
    }

    public function validate($class)
    {
        $filter = new UnderscoreNamingStrategy;
        $reflection = new ReflectionClass($class);
        $members = array_keys($reflection->getDefaultProperties());
        $errors = [];

        foreach ($members as $name) {
            $base = ucfirst($filter->hydrate($name));

            if (!method_exists($class, 'get' . $base)) {
                $errors[] = 'get' . $base;
            }
            if (!method_exists($class, 'set' . $base)) {
                $errors[] = 'set' . $base;
            }
        }

        $this->filterErrors($errors, $class);

        return $errors;
    }

    public function validateAll()
    {
        $errors = [];

        foreach ($this->getKnownClasses() as $class) {
            $result = $this->validate($class);
            if (!empty($result)) {
                $errors[$class] = $result;
            }
        }

        return $errors;
    }

    private function getKnownClasses()
    {
        $metadata = $this->entities->getMetadataFactory()->getAllMetadata();
        $classes = [];

        foreach ($metadata as $meta) {
            $classes[] = $meta->getName();
        }

        sort($classes);

        return $classes;
    }

    private function filterErrors(array & $errors, $class)
    {
        $globals = array_filter($this->ignore, 'is_string');
        $errors = array_diff($errors, $globals);

        if (isset($this->ignore[$class])) {
            $errors = array_diff($errors, $this->ignore[$class]);
        }

        return $errors;
    }
}
