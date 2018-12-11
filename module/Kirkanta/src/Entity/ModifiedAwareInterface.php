<?php

namespace Kirkanta\Entity;

use DateTime;

/**
 * Tracks entity change time.
 */
interface ModifiedAwareInterface extends CreatedAwareInterface
{
    public function getModified();
    public function setModified(DateTime $time);
}
