<?php

namespace Kirkanta\Ptv\Util;

class Language
{
    public static function isAllowed($lang)
    {
        return in_array($lang, ['fi', 'en', 'sv'], true);
    }
}
