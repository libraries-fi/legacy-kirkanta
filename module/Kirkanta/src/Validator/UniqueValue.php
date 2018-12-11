<?php

namespace Kirkanta\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Zend\Validator\AbstractValidator;

class UniqueValue extends AbstractValidator
{
    const VALUE_EXISTS = 'valueExists';

    protected $options = [
        'entity_class' => null,
        'entity_id' => null,
        'field' => null,
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
            $this->abstractOptions['messageTemplates'][self::VALUE_EXISTS] = $this->translator->translate('The value exists in database and has to be unique.');
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    public function isValid($value, $context = [])
    {
        $class = $this->getOption('entity_class');
        $id = $this->getOption('entity_id');
        $field = $this->getOption('field');
        $match = $this->entities->getRepository($class)->findOneBy([$field => $value]);

        if ($match && $match->getId() != $id) {
            $this->error(self::VALUE_EXISTS);
            return false;
        }

        return true;
    }
}
