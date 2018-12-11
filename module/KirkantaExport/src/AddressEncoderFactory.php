<?php

namespace Kirkanta\Export;

use Exception;

class AddressEncoderFactory
{
    private $labels;

    public static function create($format, array $options = [])
    {
        return (new static())->createEncoder($format, $options);
    }

    public function __construct()
    {
        $this->labels = [
            'Kirjasto',
            'Sähköposti',
            'Käyntiosoite',
            'Postiosoite',
            'Koordinaatit',
        ];
    }

    public function createEncoder($format, array $options)
    {
        $labels = $this->labels;

        if (empty($options['with_coordinates'])) {
            array_pop($labels);
        }

        switch ($format) {
            case 'csv':
                $encoder = new Encoder\Csv($labels, $options);
                $encoder->transform(function($row) use($options) {
                    $data = [
                        $row['name'],
                        $row['email'],
                        sprintf('%s, %s %s', $row['a_street'], $row['a_zipcode'], $row['a_area']),
                    ];

                    if (empty($row['m_zipcode'])) {
                        $data[] = $data[2];
                    } elseif (empty($row['m_box'])) {
                        $data[] = sprintf('%s, %s %s', $row['m_street'], $row['m_zipcode'], $row['m_area']);
                    } else {
                        $data[] = sprintf('PL %d, %s %s', $row['m_box'], $row['m_zipcode'], $row['m_area']);
                    }

                    if (!empty($options['with_coordinates'])) {
                        $data[] = $row['coordinates'];
                    }

                    return $data;
                });
                if (!empty($options['group_by'])) {
                    $encoder->groupBy('a_area');
                }
                return $encoder;

            default:
                throw new Exception(sprintf('Invalid type "%s"', $format));
        }

    }
}
