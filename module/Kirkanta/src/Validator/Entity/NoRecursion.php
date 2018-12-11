<?php

namespace Kirkanta\Validator\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Zend\Validator\AbstractValidator;

/**
 * NOTE: In the context of this validator the object bound to form is the candidate (object to be inserted)
 * and the value passed to this validator is the root of the existing chain. This validator checks
 * that the candidate object is not already contained in the chain, so that we're not causing
 * a recursive loop by inserting it for a second time.
 */
class NoRecursion extends AbstractValidator
{
    const RECURSION_DETECTED = 'recursionDetected';

    protected $options = [
        // Method that is used to access the next object in the hierarchy.
        'getter' => null,

        // Class of the bound form object.
        'entity_class' => null,

        // Field that will be used to load the entity that is identified by the value passed to
        // this validator.
        'field' => 'id'
    ];

    private $entities;
    private $translator;

    public static function create(ContainerInterface $container, array $options)
    {
        return new static(
            $container->get('Doctrine\ORM\EntityManager'),
            $container->get('MvcTranslator'),
            $options
        );
    }

    public function __construct(EntityManagerInterface $entities, $translator, array $options = null)
    {
        parent::__construct($options);
        $this->entities = $entities;
        $this->translator = $translator;

        try {
            $this->abstractOptions['messageTemplates'][self::RECURSION_DETECTED] = $this->translator->translate('Selected value is invalid because it causes recursion.');
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    public function isValid($root_id, $context = [])
    {
        if (!$root_id) {
            return true;
        }

        $candidate_id = $this->getOption('entity_id');
        $class = $this->getOption('entity_class');
        $field = $this->getOption('field');
        $root = $this->entities->getRepository($class)->findOneBy([$field => $root_id]);
        $candidate = $this->entities->getRepository($class)->findOneBy([$field => $candidate_id]);
        $method = $this->getOption('getter');

        while ($root) {
            if ($candidate == $root) {
                $this->error(self::RECURSION_DETECTED);
                return false;
            }
            $root = call_user_func([$root, $method]);
        }

        return true;
    }
}
