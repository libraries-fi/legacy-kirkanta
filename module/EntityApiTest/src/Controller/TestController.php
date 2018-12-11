<?php

namespace KirkantaTest\EntityApi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
use KirkantaTest\EntityApi\EntityValidator;

class TestController extends AbstractActionController
{
    private $validator;

    public static function create(ServiceLocatorInterface $services)
    {
        return new static($services->get(EntityValidator::class));
    }

    public function __construct(EntityValidator $validator)
    {
        $this->validator = $validator;
    }

    public function entityApiAction()
    {
        $result = $this->validator->validateAll();
        return [
            'result' => $result,
        ];
    }
}
