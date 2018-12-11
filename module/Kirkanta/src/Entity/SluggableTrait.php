<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

trait SluggableTrait
{
    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $slug;

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug ?: null;
    }
}
