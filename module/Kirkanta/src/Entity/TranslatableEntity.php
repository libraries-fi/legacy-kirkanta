<?php

namespace Kirkanta\Entity;

abstract class TranslatableEntity extends Entity implements TranslatableEntityInterface
{
    use TranslatableEntityTrait;
}
