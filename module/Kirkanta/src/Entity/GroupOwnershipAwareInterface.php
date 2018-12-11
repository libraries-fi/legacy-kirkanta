<?php

namespace Kirkanta\Entity;

interface GroupOwnershipAwareInterface
{
    public function getGroup();
    public function setGroup(Role $group = null);
}
