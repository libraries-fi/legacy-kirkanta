<?php

namespace Kirkanta\Entity;

interface ServiceInterface
{
    public function getName();
    public function setName($name);

    public function getDescription();
    public function setDescription($description);

    public function getType();
}
