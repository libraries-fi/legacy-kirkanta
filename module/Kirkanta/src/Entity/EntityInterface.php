<?php

namespace Kirkanta\Entity;

use DateTime;

/**
 * Minimal interface for defining entities
 */
interface EntityInterface
{
    public function getId();
    public function isNew();
}
