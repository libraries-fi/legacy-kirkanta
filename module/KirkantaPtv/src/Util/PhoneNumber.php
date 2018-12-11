<?php

namespace Kirkanta\Ptv\Util;

class PhoneNumber
{
    public static function normalize($number)
    {
        return preg_replace('/\D/', '', $number);
    }
}
