<?php

namespace LegacyIndexing;

final class ArrayCompare
{
    public static function diff(array $reference, array $test)
    {
        $diff = [];
        foreach (array_diff_key($reference, $test) as $key => $value) {
            if (array_key_exists($key, $reference)) {
                $diff[$key] = '__MISSING__';
            } else {
                $diff['__extra'][$key] = $value;
            }
        }
        foreach (array_intersect_key($reference, $test) as $key => $value) {
            if (is_array($value) and is_array($test[$key])) {
                if ($subdiff = self::diff($value, $test[$key])) {
                    $diff[$key] = $subdiff;
                }
            }
        }
        return self::sort($diff);
    }

    public static function sort(array &$array)
    {
        ksort($array);
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                self::sort($value);
            }
        }
        return $array;
    }
}
