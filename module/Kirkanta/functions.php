<?php

function array_get(array $array, $key, $default = null)
{
    if (!array_key_exists($key, $array)) {
        return $default;
    }

    return $array[$key];
}

function array_take(array & $array, $key, $default = null)
{
    $value = array_get($array, $key, $default);
    unset($array[$key]);
    return $value;
}

function array_filter_keys(array $array, array $keys, $reverse = false)
{
    $clean = [];
    if ($reverse) {
        foreach ($keys as $key) {
            unset($array[$key]);
        }
        $clean = $array;
    } else {
        foreach ($array as $key => $value) {
            if (in_array($key, $keys)) {
                $clean[$key] = $value;
            }
        }
    }

    return $clean;
}
