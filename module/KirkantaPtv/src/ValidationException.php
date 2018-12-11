<?php

namespace Kirkanta\Ptv;

use Exception;

class ValidationException extends Exception
{
    public $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation failed' . PHP_EOL . PHP_EOL . print_r($errors, true));
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
