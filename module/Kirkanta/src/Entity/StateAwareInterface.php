<?php

namespace Kirkanta\Entity;

interface StateAwareInterface
{
    public function getState();
    public function setState($state);
    public function isPublished();
}
