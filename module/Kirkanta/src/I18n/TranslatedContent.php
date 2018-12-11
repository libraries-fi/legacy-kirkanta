<?php

namespace Kirkanta\I18n;

class TranslatedContent
{
    public static function mergeTranslations(array $data, array $translations = null, $fields = null)
    {
        $langs = ContentLanguages::create();

        if (is_null($translations)) {
            $translations = isset($data['translations']) ? $data['translations'] : [];
            unset($data['translations']);
        }
        if (is_null($fields)) {
            $fields = array_keys(reset($translations) ?: []);
        }
        foreach ($fields as $key) {
            $value = isset($data[$key]) ? $data[$key] : null;
            $data[$key] = [$langs->getDefaultLocale() => $value];
            foreach ($langs->getLocales() as $lang) {
                if ($lang != $langs->getDefaultLocale()) {
                    $data[$key][$lang] = isset($translations[$lang][$key])
                        ? $translations[$lang][$key]
                        : null;
                }
            }
        }
        return $data;
    }
}
