<?php

namespace Kirkanta\Entity;

/**
 * Interface for entities that are templatable i.e. some instances can
 * be linked to by other instances and they share their template's data.
 */
interface TemplateEntityInterface
{
    /**
     * Display name for the entity
     */
    public function getLabel();
}
