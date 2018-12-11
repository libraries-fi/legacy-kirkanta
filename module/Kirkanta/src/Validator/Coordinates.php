<?php

namespace Kirkanta\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Translator\TranslatorInterface;

class Coordinates extends AbstractValidator
{
    const INVALID_VALUE = 'invalidValue';

    private $translator;

    public static function create(ContainerInterface $container, array $options)
    {
        return new static(
            $container->get('MvcTranslator'),
            $options
        );
    }

    public function __construct(TranslatorInterface $translator, array $options = null)
    {
        parent::__construct($options);
        $this->translator = $translator;
        $this->abstractOptions['messageTemplates'][self::INVALID_VALUE] = $this->translator->translate('Coordinates have to be defined as a pair of numbers, for example: 20.12345, 51.09876');
    }

    public function isValid($value)
    {
        $parts = array_map('trim', explode(',', $value));
        $valid = count(array_filter(array_map('is_numeric', $parts))) == 2;

        if (!$valid) {
            $this->error(self::INVALID_VALUE);
            return false;
        }

        return true;
    }
}
