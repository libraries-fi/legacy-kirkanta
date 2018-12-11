<?php

namespace Kirkanta\Ptv;

use DateTime;
use Kirkanta\Entity\Organisation;
use Kirkanta\Ptv\Entity\Meta;
use Kirkanta\Ptv\Util\Address;
use Kirkanta\Ptv\Util\Language;
use Kirkanta\Ptv\Util\Municipalities;
use Kirkanta\Ptv\Util\OpeningTimes;
use Kirkanta\Ptv\Util\PhoneNumber;
use Kirkanta\Ptv\Util\Text;

class ServiceLocationMapper
{
    protected $municipalities;

    public function __construct()
    {
        $this->municipalities = new Municipalities;
    }

    /**
     * Supported entity type ID in Kirkanta.
     */
    public function getTypeId()
    {
        return 'organisation';
    }

    public function convert(Organisation $organisation, Meta $meta) {
        $doc = [
            'organizationId' => null, // $organisation->getPtvMeta()->organisationId()
            'sourceId' => 'kir-o-' . $organisation->getId(),

            'organizationId' => 'b1d2df1c-aa6d-480b-ac48-e0ce18c17597',
            'languages' => [],

            // 'language' => 'fi',
            // 'publishingStatus' => $meta->isPublished() ? 'Published' : 'Draft',
            'publishingStatus' => 'Published',
            'organizationType' => 'Municipality',
            'municipality' => $this->municipalities->nameToId($organisation->getCity()->getName()),
        ];

        $languages = [];

        foreach ($organisation->getTranslatedValues('name') as $lang => $name) {
            if (!empty($name) && Language::isAllowed($lang)) {
                $languages[$lang] = true;
                $doc['serviceChannelNames'][] = [
                    'language' => $lang,
                    'value' => Text::truncate($name, 100)
                ];
            }
        }

        foreach ($organisation->getTranslatedValues('email') as $lang => $email) {
            if (!empty($email) && Language::isAllowed($lang)) {
                $doc['supportEmails'][] = [
                    'language' => $lang,
                    'value' => $email,
                ];
            }
        }

        foreach ($organisation->getTranslatedValues('description') as $lang => $description) {
            // Description is mandatory.

            if (empty($description)) {
                // $description = 'Lorem ipsum dolor sit amet.';
                continue;
            }

            if (Language::isAllowed($lang)) {
                $doc['serviceChannelDescriptions'][] = [
                    'type' => 'Description',
                    'language' => $lang,
                    'value' => Text::stripHtml($description, 4000)
                ];
            }
        }

        if (empty($doc['serviceChannelDescriptions'])) {
            foreach ($organisation->getTranslatedValues('legacy_description') as $lang => $description) {
                if (Language::isAllowed($lang)) {
                    $doc['serviceChannelDescriptions'][] = [
                        'type' => 'Description',
                        'language' => $lang,
                        'value' => Text::truncate($description, 4000)
                    ];
                }
            }
        }

        foreach ($organisation->getTranslatedValues('slogan') as $lang => $description) {
            if (Language::isAllowed($lang)) {
                $doc['serviceChannelDescriptions'][] = [
                    'type' => 'ShortDescription',
                    'language' => $lang,
                    'value' => Text::truncate(Text::stripHtml($description), 150)
                ];
            }
        }

        foreach ($organisation->getPhoneNumbers() as $number) {
            foreach ($number->getTranslatedValues('name') as $lang => $name) {
                if (!empty($name) && Language::isAllowed($lang)) {
                    $doc['supportPhones'][] = [
                        'language' => $lang,
                        'additionalInformation' => $name,
                        'number' => PhoneNumber::normalize($number->getNumber()),
                        'isFinnishServiceNumber' => true,
                        'serviceChargeType' => 'Free',
                    ];
                }
            }
        }

        foreach ($organisation->getLinks() as $link) {
            foreach ($link->getTranslatedValues('name') as $lang => $name) {
                if (!empty($name) && Language::isAllowed($lang)) {
                    $doc['attachments'] = [
                        'language' => $lang,
                        'name' => Text::truncate($name, 100),
                        'url' => $link->getTranslatedValue($lang, 'url'),
                    ];
                }
            }
        }

        if ($address = $organisation->getAddress()) {
            $doc['addresses'][0] = [
                'type' => 'Visiting',
                'postalCode' => (string)$address->getZipcode(),
            ];

            foreach ($address->getTranslatedValues('street') as $lang => $street) {
                if (!empty($street) && Language::isAllowed($lang)) {
                    list($street, $street_nr) = Address::parseStreetAndNumber($street);

                    $doc['addresses'][0]['streetAddress'][] = [
                        'language' => $lang,
                        'value' => $street,
                    ];

                    if ($street_nr) {
                        $doc['addresses'][0]['streetNumber'] = $street_nr;
                    }
                }
            }

            if ($coords = $organisation->getCoordinates()) {
                list($lat, $lon) = array_map('trim', explode(',', $coords));
                $subdoc['latitude'] = $lat;
                $subdoc['longitude'] = $lon;
            }
        }

        if ($address = $organisation->getMailAddress()) {
            $subdoc = [
                'type' => 'Postal',
                'postalCode' => (string)$address->getZipcode(),
            ];

            if ($boxnum = $address->getBoxNumber()) {
                $subdoc['postOfficeBox'] = [
                    [
                        'language' => 'fi',
                        'value' => sprintf('PL %d', $boxnum),
                    ],
                    [
                        'language' => 'en',
                        'value' => sprintf('P.O. Box %d', $boxnum),
                    ],
                    [
                        'language' => 'sv',
                        'value' => sprintf('PB %d', $boxnum),
                    ],
                ];
            }
            if ($address->getStreet()) {
                foreach ($address->getTranslatedValues('street') as $lang => $street) {
                    if (!empty($street) && Language::isAllowed($lang)) {
                        list($street, $street_nr) = Address::parseStreetAndNumber($street);
                        $subdoc['streetAddress'][] = [
                            'language' => $lang,
                            'value' => $street
                        ];

                        if (isset($street_nr)) {
                            $subdoc['streetNumber'] = $street_nr;
                        }
                    }
                }
            } else {
                foreach ($address->getTranslatedValues('area') as $lang => $area) {
                    if (!empty($area) && Language::isAllowed($lang)) {
                        $subdoc['streetAddress'][] = [
                            'language' => $lang,
                            'value' => $area,
                        ];
                    }
                }
            }
            $doc['addresses'][] = $subdoc;
        }

        foreach (OpeningTimes::filterInactivePeriods($organisation->getPeriods('default')->toArray()) as $period) {
            $subdoc = [
                'publishingStatus' => 'Published',
                'serviceHourType' => $period->isContinuous() ? 'Standard' : 'Exception',
                'validFrom' => $period->getValidFrom()->format('Y-m-d\Th:i:s+00:00'),
            ];

            if ($date = $period->getValidUntil()) {
                $subdoc['validTo'] = $date->format('Y-m-d\T23:59:59+00:00');
            }

            foreach ($period->getTranslatedValues('description') as $lang => $description) {
                if (!empty($description) && Language::isAllowed($lang)) {
                    $subdoc['additionalInformation'][] = [
                        'language' => $lang,
                        'value' => $description
                    ];
                }
            }

            $defs = OpeningTimes::weekDefinitions($period);

            foreach ($defs as $day) {
                if (!$day['closed']) {
                    foreach ($day['times'] as $i => $time) {
                        $hour = [
                            'dayFrom' => DateTime::createFromFormat('Y-m-d', $day['date'])->format('l'),
                            'from' => $time['opens'],
                            'to' => $time['closes'],
                            'isExtra' => $i > 0,
                        ];
                        $subdoc['openingHour'][] = $hour;
                    }
                }
            }

            if (empty($subdoc['openingHour'])) {
                $subdoc['isClosed'] = true;
            }

            $doc['serviceHours'][] = $subdoc;
        }

        $doc['languages'] = array_keys($languages);

        $this->validate($doc);

        return [
            'document' => $doc,
            'type' => 'ServiceChannel/ServiceLocation'
        ];
    }

    public function validate(array $document)
    {
        $errors = [];
        $languages = $document['languages'];

        if (empty($document['organizationId'])) {
            $errors[''][] = 'Metatieto organizationId puuttuu.';
        }

        foreach ($languages as $lang) {
            ;

            if ($name = $this->findItem($document['serviceChannelNames'], $lang)) {
                // $errors[$lang][] = 'Nimi on määritettävä.';
            } else {
                $errors[$lang][] = 'Nimi on määritettävä.';
            }

            if ($description = $this->findItem($document['serviceChannelDescriptions'], ['type' => 'Description', 'language' => $lang])) {
                if (mb_strlen($description['value']) < 5) {
                    $errors[$lang][] = 'Kuvauksen on oltava vähintään viisi merkkiä pitkä.';
                }
            } else {
                $errors[$lang][] = 'Kuvaus puuttuu. Sen on oltava vähintään viisi merkkiä pitkä.';
            }

            if ($slogan = $this->findItem($document['serviceChannelDescriptions'], ['type' => 'ShortDescription', 'language' => $lang])) {
                if (mb_strlen($slogan['value']) < 5) {
                    $errors[$lang][] = 'Sloganin on oltava vähintään viisi merkkiä pitkä.';
                }
            } else {
                $errors[$lang][] = 'Slogan puuttuu. Sen on oltava vähintään viisi merkkiä pitkä.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    function findItem(array $source, $filter) {
        foreach ($source as $item) {
            if (is_array($filter)) {
                $found = true;
                foreach ($filter as $key => $value) {
                    if ($item[$key] != $value) {
                        $found = false;
                        break;
                    }
                }
                if ($found) {
                    return $item;
                }
            } else {
                if ($item['language'] == $filter) {
                    return $item;
                }
            }
        }
    }
}
