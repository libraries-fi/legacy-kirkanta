<?php

namespace Kirkanta\I18n;

class Translations
{
    private static $locales;
    private static $default_language = 'fi';

    public static function defaultLanguage()
    {
      return self::$default_language;
    }

    public static function setLocales(array $locales)
    {
        self::$locales = $locales;
    }

    public static function mergeTranslations(array $document, array $translations = null)
    {
        if (is_null($translations)) {
            // if (!isset($document['translations'])) {
            //     print_r($document);
            //     exit;
            // }
            $translations = $document['translations'];
            unset($document['translations']);
        }
        foreach (array_keys(reset($translations)) as $key) {
            $document[$key] = [self::$default_language => $document[$key]];
        }
        foreach ($translations as $lang => $values) {
            foreach ($values as $key => $value) {
                $document[$key][$lang] = $value;
            }
        }
        return $document;
    }

    public static function merge(array $base, array $defaults)
    {
        foreach ($defaults as $lang => $strings)
        {
            if (!isset($base[$lang])) {
                $base[$lang] = $strings;
            } else {
                $base[$lang] += $strings;
            }
        }
        return $base;
    }

    public static function filterEmpty(array $translations)
    {
        /**
         * NOTE: Decided not to remove empty fields as they might be useful as
         * placeholders.
         */
        return $translations;

        // foreach ($translations as $lang => &$strings) {
        //     print_r($strings);
        //     $strings = array_filter($strings, 'strlen');
        //     if (!$strings) {
        //         unset($translations[$lang]);
        //     }
        // }
        // return $translations;
    }

    public static function extractField(array $translations, $field)
    {
        $values = [];
        foreach (self::$locales as $lang) {
            $values[$lang] = isset($translations[$lang]) ? array_get($translations[$lang], $field) : null;
        }
        return $values;
    }
}
