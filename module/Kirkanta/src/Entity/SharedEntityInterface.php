<?php

namespace Kirkanta\Entity;

/**
 * Signals that instances can be shared between multiple instances of other type
 * of entity. Meaning if 'shared' might vary or it might be identical to being
 * a template.
 */
interface SharedEntityInterface
{
    public function isShared();
    public function getShared();
    public function setShared($shared);
}
