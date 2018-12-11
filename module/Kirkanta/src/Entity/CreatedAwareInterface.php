<?php

namespace Kirkanta\Entity;

use DateTime;

interface CreatedAwareInterface
{
    public function getCreated();
    public function setCreated(DateTime $time);
}
