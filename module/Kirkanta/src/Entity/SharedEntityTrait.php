<?php

namespace Kirkanta\Entity;

trait SharedEntityTrait
{
    public function isShared()
    {
        return $this->shared;
    }

    public function getShared()
    {
        return $this->isShared();
    }

    public function setShared($shared)
    {
        $this->shared = (bool)$shared;
    }
}
