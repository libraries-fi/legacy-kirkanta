<?php

namespace Kirkanta\Ptv\Util;

class Address
{
    public static function parseStreetAndNumber($address)
    {
        $parts = explode(' ', $address);
        $street_nr = (int)end($parts);
        if ($street_nr > 0) {
            array_pop($parts);
            $street = implode(' ', $parts);

            return [$street, $street_nr];
        } else {
            return [$address, null];
        }
    }
}
