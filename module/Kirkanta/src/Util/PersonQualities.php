<?php

namespace Kirkanta\Util;

use Zend\I18n\Translator\TranslatorInterface;

class PersonQualities
{
    protected $qualities;

    public function __construct(TranslatorInterface $translator)
    {
        $tr = [$translator, 'translate'];
        $this->qualities = [
            'av_production' => $tr('AV production'),
            'book_tips' => $tr('Book tips'),
            'children_library' => $tr('Children\'s library work'),
            'communications' => $tr('Communications'),
            'e_publifications' => $tr('Digital publishing'),
            'education_planning' => $tr('Education planning'),
            'education' => $tr('Education'),
            'finances' => $tr('Finances'),
            'games' => $tr('Games'),
            'human_resources' => $tr('Human resources'),
            'immigrant_library_work' => $tr('Immigrant library work'),
            'indexing' => $tr('Indexing'),
            'information_retrieval' => $tr('Information retrieval'),
            'information_technology' => $tr('Information technology'),
            'international_cooperation' => $tr('International cooperation'),
            'library_facility_work' => $tr('Library facility work'),
            'library_systems' => $tr('Library systems'),
            'literature' => $tr('Literature'),
            'media_education' => $tr('Media education'),
            'mobile_libraries' => $tr('Mobile libraries'),
            'music_library_work' => $tr('Music library work'),
            'pedagogy' => $tr('Pedagogy'),
            'remote_services' => $tr('Remote services'),
            'seeking_library_work' => $tr('Seeking library work'),
            'statistics' => $tr('Statistics'),
            'web_services' => $tr('Web services'),
            'youth_library_work' => $tr('Youth library work'),

            'collections' => $tr('Collections'),
            'customer_service' => $tr('Customer service'),
            'materials_purchases' => $tr('Materials purchases'),
            'materials_selection' => $tr('Materials selection'),
        ];
        asort($this->qualities);
    }

    public function getQualities()
    {
        return $this->qualities;
    }

    public function map($type)
    {
        return isset($this->types[$type]) ? $this->types[$type] : null;
    }
}
