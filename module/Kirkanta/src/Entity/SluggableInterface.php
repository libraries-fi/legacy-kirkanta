<?php

namespace Kirkanta\Entity;

interface SluggableInterface
{
    public function getSlug();
    public function setSlug($slug);
}
