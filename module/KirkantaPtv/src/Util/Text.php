<?php

namespace Kirkanta\Ptv\Util;

use Html2Text\Html2Text;

class Text
{
    public static function stripHtml($html)
    {
        $text = (new Html2Text($html))->getText();
        return $text;
    }

    public static function truncate($text, $maxlen)
    {
        $offset = 0;
        $matches = [];
        while ($offset <= $maxlen) {
            if (preg_match('/[\!\?\.]+/', $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $pos = strlen($matches[0][0]) + $matches[0][1];
                if ($pos > $maxlen) {
                    $text = substr($text, 0, $offset);
                    break;
                } else {
                    $offset = $pos;
                }
            } else {
                $text = substr($text, 0, $offset ?: $maxlen);
                break;
            }
        }
        return $text;
    }
}
