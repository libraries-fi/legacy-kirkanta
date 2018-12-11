<?php

namespace Kirkanta\Util;

use Kirkanta\Entity;

class OrganisationResources
{
    protected $mapping;

    public function __construct()
    {
        $this->mapping = [
            'accessibility' => Entity\AccessibilityReference::class,
            'link_groups' => Entity\OrganisationWebLinkGroup::class,
            'links' => Entity\OrganisationWebLink::class,
            'periods' => Entity\Period::class,
            'persons' => Entity\Person::class,
            'phone_numbers' => Entity\PhoneNumber::class,
            'pictures' => Entity\Picture::class,
            'services' => Entity\Service::class,
        ];
    }
    public function classForSection($section)
    {
        if (isset($this->mapping[$section])) {
            return $this->mapping[$section];
        }
    }
}
