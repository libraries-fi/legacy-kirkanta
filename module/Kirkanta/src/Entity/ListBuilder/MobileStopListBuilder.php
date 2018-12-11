<?php

namespace Kirkanta\Entity\ListBuilder;

class MobileStopListBuilder extends OrganisationListBuilder
{
    public function build($entities)
    {
        return $this->table()
            ->setData($entities)
            ->setColumns([
                'name' => $this->tr('Name'),
                'address' => $this->tr('Address'),
            ])
            ->setWidth('state', 30)
            ->setSortable('name', true)
            ->transform('name', [$this, 'editLink'])
            ->transform('address', function($address) {
                if ($address) {
                    return sprintf('%s, %s %s', $address->getStreet(), $address->getZipcode(), $address->getCity()->getName());
                }
            });
    }
}
