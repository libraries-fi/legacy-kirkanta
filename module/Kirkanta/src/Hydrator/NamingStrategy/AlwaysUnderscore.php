<?php

namespace Kirkanta\Hydrator\NamingStrategy;

use Zend\Filter\FilterChain;
use Zend\Hydrator\NamingStrategy\NamingStrategyInterface;

class AlwaysUnderscore implements NamingStrategyInterface
{
    private $filters;

    private function getFilterChain()
    {
        if (!$this->filters) {
            $this->filters = new FilterChain;
            $this->filters->attachByName('WordCamelCaseToUnderscore');
            $this->filters->attachByName('StringToLower');
        }
        return $this->filters;
    }

    public function hydrate($value)
    {
        return $this->getFilterChain()->filter($value);
    }

    public function extract($value)
    {
        return $this->hydrate($value);
    }
}
