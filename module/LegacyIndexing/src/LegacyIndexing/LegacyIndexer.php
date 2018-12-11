<?php

namespace LegacyIndexing;

use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Client;
use Kirkanta\Entity\Address;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\Person;
use Kirkanta\Entity\ServiceType;
use Kirkanta\I18n\ContentLanguages;
use KirkantaIndexing\Hydrator\AnnotatedHydrator;
use Zend\ServiceManager\ServiceLocatorInterface;

class LegacyIndexer
{
    protected $em;
    protected $es;

    protected $bulk = [];
    protected $bulkDelete = [];
    protected $entities = [];

    public static function create(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('Config')['elastic_legacy'];
        $host = sprintf('%s:%d', $config['host'], $config['port']);

        return new static(
            $sm->get('Doctrine\ORM\EntityManager'),
            new Client(['hosts' => [$host]]),
            new ContentLanguages($sm->get('MvcTranslator')),
            $sm->get('Config')
        );
    }

    public function __construct(EntityManagerInterface $em, Client $es, ContentLanguages $langs, array $config)
    {
        $this->em = $em;
        $this->es = $es;
        $this->langs = $langs;
        $this->config = $config;
    }

    public function __destruct()
    {
        $this->flush();
    }

    public function flush()
    {
        foreach ($this->bulk as $sub) {
            $query['body'][] = [
                'index' => [
                    '_index' => $sub['index'],
                    '_type' => $sub['type'],
                    '_id' => $sub['id'],
                ],
            ];
            $query['body'][] = $sub['body'];
        }
        foreach ($this->bulkDelete as $sub) {
            $query['body'][] = [
                'delete' => [
                    '_index' => $sub['index'],
                    '_type' => $sub['type'],
                    '_id' => $sub['id'],
                ]
            ];
        }

        $this->bulk = [];
        $this->bulkDelete = [];

        if (isset($query)) {
            $responses = $this->es->bulk($query);
            // foreach ($responses['items'] as $response) {
            //     $entity = array_shift($this->entities);
            //     if (!$entity->getElasticId() && isset($response['index']['_id'])) {
            //         $entity->setElasticId($response['index']['_id']);
            //         $this->em->persist($entity);
            //     }
            // }
            // $this->em->flush();
        }
    }

    public function index($entity, array $document)
    {
        // var_dump(get_class($entity));
        // exit('index');

        if ($entity instanceof Organisation) {
            if ($entity->isPublished()) {
                $mapped = $this->convertOrganisation($entity);
                $this->bulk[] = [
                    'index' => $this->config['elastic_legacy']['index'],
                    'type' => 'organisation',
                    'id' => $entity->getElasticId() ?: $entity->getId(),
                    'body' => $mapped,
                ];
                $this->entities[] = $entity;
            } else {
                $this->remove($entity, $document);
            }
        }

        if ($entity instanceof Person) {
            if ($entity->isPublished()) {
                $mapped = $this->convertPerson($entity);
                $this->bulk[] = [
                    'index' => $this->config['elastic_legacy']['index'],
                    'type' => 'person',
                    'id' => $entity->getElasticId() ?: $entity->getId(),
                    'body' => $mapped,
                ];
                $this->entities[] = $entity;
            } else {
                $this->remove($entity, $document);
            }
        }

        if ($entity instanceof ServiceType) {
            $mapped = $this->convertService($entity);
            $this->bulk[] = [
                'index' => $this->config['elastic_legacy']['index'],
                'type' => 'service',
                'id' => $entity->getElasticId() ?: $entity->getId(),
                'body' => $mapped,
            ];
            $this->entities[] = $entity;
        }
    }

    public function remove($entity, array $document)
    {
        if ($entity instanceof Organisation) {
            $this->bulkDelete[] = [
                'index' => $this->config['elastic_legacy']['index'],
                'type' => 'organisation',
                'id' => $entity->getElasticId() ?: $entity->getId(),
            ];
        }
        if ($entity instanceof ServiceType) {
            $this->bulkDelete[] = [
                'index' => $this->config['elastic_legacy']['index'],
                'type' => 'service',
                'id' => $entity->getElasticId() ?: $entity->getId(),
            ];
        }
        if ($entity instanceof Person) {
            $this->bulkDelete[] = [
                'index' => $this->config['elastic_legacy']['index'],
                'type' => 'person',
                'id' => $entity->getElasticId() ?: $entity->getId(),
            ];
        }
    }

    protected function convertService(ServiceType $service)
    {
        $typemap = [
            'service' => 'palvelu',
            'room' => 'tila',
            'hardware' => 'laite',
            'web_service' => 'verkkopalvelu',
            'collection' => 'kokoelma',
        ];

        $document = [
            // 'price' => $service->getPrice(),
            // 'for_loan' => $service->isForLoan(),
            'price' => '',
            'for_loan' => false,
            'servicetype_priority' => $service->getHelmetTypePriority(),
            'type' => $typemap[$service->getType()],
            'meta' => [
                // No group info in Service entity anymore, so we hardcode this
                // to helmet, because they are the only ones to fetch service data
                // via API.
                'group' => 'helmet'
            ]
        ];

        $translations = $service->getTranslations();
        $document += $this->translated('name', $service->getName(), $translations);
        $document += $this->translated('description', $service->getDescription(), $translations, 'description_long');
        $document += $this->translated('short_description', $service->getDescription(), $translations, 'description_short');

        if (empty($document['name_fi'])) {
            $template = $service->getTemplate();
            $document = array_merge($document, $this->translated('name', $template->getName(), $template->getTranslations()));
        }
        return $document;
    }

    protected function convertPerson(Person $person)
    {
        $organisation = $person->getOrganisation();
        $organisation_id = $organisation ? ($organisation->getElasticId() ?: $organisation->getId()) : null;
        $document = [
            'organisation' => $organisation_id,
            'first_name' => $person->getFirstName(),
            'last_name' => $person->getLastName(),
            'is_head' => (bool)$person->isHead(),
            'responsibility_fi' => $person->getResponsibility(),
            'responsibility_en' => $person->getResponsibility(),
            'responsibility_ru' => $person->getResponsibility(),
            'responsibility_sv' => $person->getResponsibility(),
            'job_title_fi' => $person->getJobTitle(),
            'job_title_en' => $person->getJobTitle(),
            'job_title_ru' => $person->getJobTitle(),
            'job_title_sv' => $person->getJobTitle(),
            'qualities' => $person->getQualities(),
            'contact' => [
                'telephone' => $person->getPhone() ?: "",
                'email' => $person->getEmail() ?: "",
                'public_email' => $person->isEmailPublic(),
                'url_fi' => $person->getUrl(),
                'url_en' => "",
                'url_sv' => "",
                'url_ru' => "",
            ],
            'meta' => [
                'document_state' => 'published',
                'created' => $person->getCreated()->format('Y-m-d\TH:i:s'),
                'modified' => $person->getModified()->format('Y-m-d\TH:i:s'),
            ],
        ];
        return $document;
    }

    protected function convertOrganisation(Organisation $entity)
    {
        // var_dump($entity->getCachedLegacyTimes());
        // exit('index organisation');

        $day_names = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $periods = [];

        foreach ($entity->getPeriods('default') as $i => $period) {
            $item = [
                'start' => $period->getValidFrom()->format('Y-m-d') . 'T00:00:00',
                'end' => $period->getValidUntil() ? $period->getValidUntil()->format('Y-m-d') . 'T23:59:59' : null,
                'default_period' => $period->isContinuous(),
                'closed_completely' => $period->isClosedCompletely(),
            ];

            // $days = array_pad($period->getDays(), 7, ['times' => [], 'info' => null, 'translations' => []]);
            $days = $period->getDays();

            foreach ($days as $i => $day) {
                if ($i > 6) {
                    // For now, let's skip periods longer than good ol' seven days.
                    break;
                }

                $day_i = $period->getWeight() < 7 ? ($period->getValidFrom()->format('N') - 1 + $i) % 7 : $i;
                $prefix = $day_names[$day_i] . '_';
                $opens = empty($day['times']) ? null : $day['times'][0]['opens'];
                $closes = empty($day['times']) ? null : end($day['times'])['closes'];
                $item[$prefix . 'start'] = intval(str_replace(':', '', ltrim($opens, 0))) ?: null;
                $item[$prefix . 'end'] = intval(str_replace(':', '', ltrim($closes, 0))) ?: null;
                $item += $this->translated('info', $day['info'], $day['translations'] ?? [], $prefix . 'description');
            }

            $item += $this->translated('name', $period->getName(), $period->getTranslations());
            $item += $this->translated('description', $period->getDescription(), $period->getTranslations());
            $periods[] = $item;
        }

        usort($periods, function($a, $b) {
            return strcasecmp($a['start'], $b['start']);
        });

        $parent = $entity->getParent();
        $parent_id = $parent ? ($parent->getElasticId() ?: $parent->getId()) : null;

        $document = [
            'consortium' => '',
            'provincial_area' => '',
            'period' => $periods,
            'schedules' => $entity->getCachedLegacyTimes(),
            'isil' => $entity->getIsil(),
            'branch_type' => $entity->getBranchType(),
            'organisation_type' => $entity->getType(),
            'coordinates' => $entity->getCoordinates() ?: '',
            'homepage' => $entity->getHomepage(),
            'identificator' => $entity->getIdentificator(),
            'established_year' => $entity->getFounded(),
            'parent_organisation' => $parent_id,
            'additional_info' => [
                'slug' => $entity->getSlug(),
                'extrainfo' => [],
            ],
            'contact' => [
                'web_library_url' => $entity->getWebLibrary(true),
                'building_architect' => $entity->getBuildingArchitect(),
                'coordinates' => $entity->getCoordinates() ?: '',
                'email' => $entity->getEmail(),
                'building_interior_designer' => $entity->getInteriorDesigner(),
                'building_year' => $entity->getConstructionYear(),

                'street_address' => [
                    'post_code' => $entity->getAddress() ? $entity->getAddress()->getZipcode() : null,
                ],
                'mail_address' => [
                ]
            ],
            'meta' => [
                'document_state' => 'published',
                'created' => $entity->getCreated()->format('Y-m-d\TH:i:s'),
                'modified' => $entity->getModified()->format('Y-m-d\TH:i:s'),
                'links_updated' => '2012-01-01T00:00:00',

                'editor' => null,
                'creator' => $entity->getGroup()->getRoleId(),
                'group' => $entity->getGroup()->getRoleId(),
            ],
            'accessibility' => [
                'accessible_entry' => false,
                'accessible_toilet' => false,
                'accessible_parking' => false,
                'induction_loop' => false,
                'large_typeface_collection' => false,
                'lift' => false,
                'extraaccessibilityinfo' => [],
            ],
        ];

        if ($entity->getCity() and $library = $entity->getCity()->getProvincialLibrary()) {
            $document['provincial_area'] = $library->getLegacyId();
        }

        if ($consortium = $entity->getConsortium(true)) {
            $allowed_types = ['library', 'facility'];
            $ignored_branches = ['other', 'polytechnic', 'university', 'vocational_college', 'special'];

            if (in_array($entity->getType(), $allowed_types, true) && !in_array($entity->getBranchType(), $ignored_branches, true)) {
                $document['consortium'] = $consortium->getLegacyId() ?: '';
            } else {
                $document['_internal']['consortium'] = '';
            }
        }

        if ($addr = $entity->getMailAddress() ?: new Address) {
            $translations = $addr->getTranslations();
            $document['contact']['mail_address'] = [
                'post_box' => $addr->getBoxNumber(),
                'post_code' => $addr->getZipcode(),
            ];

            $document['contact']['mail_address'] += $this->translated('street', $addr->getStreet() ?: '', $translations, 'post_address');
            $document['contact']['mail_address'] += $this->translated('area', $addr->getArea() ?: '', $translations, 'post_office');
        }

        $translations = $entity->getTranslations();

        $telephones = $entity->getPhoneNumbers()->toArray();

        usort($telephones, function($a, $b) {
            return $a->getId() - $b->getId();
        });

        $document['contact']['telephones'] = array_map(function($n) {
            return ['telephone_number' => $n->getNumber()]
                + $this->translated('name', $n->getName(), $n->getTranslations(), 'telephone_name')
                + $this->translated('description', $n->getDescription(), $n->getTranslations(), 'telephone_description');
        }, $telephones);

        $document['contact']['internet'] = array_map(function($w) {
            $translations = $w->getTranslations();
            return $this->translated('name', $w->getName(), $w->getTranslations())
                + $this->translated('url', $w->getUrl(), $w->getTranslations())
                + $this->translated('description', $w->getDescription(), $w->getTranslations(), 'url_description');
        }, $entity->getLinks()->toArray());

        $document['services'] = array_map(function($s) {
            $translations = $this->mergeServiceTranslations($s->getTranslations(), $s->getTemplate()->getTranslations());

            $typemap = [
                'service' => 'palvelu',
                'room' => 'tila',
                'hardware' => 'laite',
                'web_service' => 'verkkopalvelu',
                'collection' => 'kokoelma',
            ];

            $model_id = $s->getElasticId() ?: $s->getId();

            $subdoc = [
                'servicetype_priority' => $s->getHelmetTypePriority(),
                'service_priority' => $s->getHelmetPriority(),
                'tag' => [],
                'for_loan' => $s->isForLoan(),
                'price' => $s->getPrice(),
                'type' => $typemap[$s->getType()],
                'model' => $model_id,
                'image' => $s->getPicture(),
                'image_instance' => $s->getPicture(),
                'contact' => [
                    'email' => $s->getEmail(),
                    'telephone' => $s->getPhoneNumber(),
                ] + $this->translated('website', $s->getWebsite(), $translations, 'url'),
            ]
                + $this->translated('name', $s->getName(), $translations)
                + $this->translated('short_description', $s->getShortDescription(), $translations, 'description_short')
                + $this->translated('name', $s->getName(), $translations, 'instance_name')
                + $this->translated('description', $s->getDescription(), $translations, 'description_long');

            foreach ($s->getTemplate()->getTranslations() as $lang => $data) {
                if (empty($subdoc['name_' . $lang]) && isset($data['name'])) {
                    $subdoc['name_' . $lang] = $data['name'];
                }
            }

            if (empty($subdoc['name_fi'])) {
                $template = $s->getTemplate();
                $subdoc = array_merge($subdoc, $this->translated('name', $template->getName(), $template->getTranslations()));
            }

            return $subdoc;
        }, $entity->getServices()->toArray());

        $document['additional_info']['extrainfo'] = array_map(function($info) {
            return [
                'property_label_fi' => $info['title'],
                'property_value_fi' => $info['value'],
            ] + call_user_func_array('array_merge', array_map(function($trdata, $lang) {
                return [
                    'property_label_' . $lang => $trdata['title'],
                    'property_value_' . $lang => $trdata['value'],
                ];
            }, $info['translations'], array_keys($info['translations'])));
        }, $entity->getCustomData());

        // var_dump($document['additional_info']['extrainfo']);

        $document['additional_info']['extrainfo'] = array_filter($document['additional_info']['extrainfo'], function($item) {
            return !empty($item['property_label_fi']);
        });

        if (count($entity->getPictures())) {
            // Set default value as not every Organisation has any picture defined as 'default'
            // due to it being introduced afterwards.
            $document['default_attachment'] = 0;

            $document['attachments'] = array_map(function($p, $i) {
                return [
                    'author' => $p->getAuthor(),
                    'default' => $p->isDefault(),
                    'file' => $p->getFilename(),
                    'permissions' => '',
                    'year' => $p->getYear() ?: null,
                ] + $this->translated('name', $p->getName(), $p->getTranslations());
            }, $entity->getPictures()->toArray(), range(0, count($entity->getPictures())-1));

            foreach ($document['attachments'] as $i => $picture) {
                if ($picture['default']) {
                    $document['default_attachment'] = $i;
                    break;
                }
            }
        } else {
            $document['default_attachment'] = null;
        }

        $document['contact'] += $this->translated('homepage', $entity->getHomepage(), $translations);
        $document['contact'] += $this->translated('building_name', $entity->getBuildingName(), $translations, 'building');
        $document['contact'] += $this->translated('transit_directions', $entity->getTransitDirections(), $translations, 'directions');
        $document['contact'] += $this->translated('parking_instructions', $entity->getParkingInstructions(), $translations, 'parking');

        if ($address = $entity->getAddress()) {
            $document['contact']['street_address'] += $this->translated('area', $address->getArea(), $address->getTranslations());
            $document['contact']['street_address'] += $this->translated('street', $address->getStreet(), $address->getTranslations());
            $document['contact']['street_address'] += $this->translated('name', $address->getCity()->getName(), $address->getCity()->getTranslations(), 'municipality');
        }

        $document += $this->translated('name', $entity->getName(), $translations);
        $document += $this->translated('legacy_description', $entity->getLegacyDescription(), $translations, 'description');
        $document += $this->translated('short_name', $entity->getShortName(), $translations, 'name_short');

        if (empty($document['name_sv'])) {
            $document['name_sv'] = $document['name_fi'];
        }

        if (empty($document['name_en'])) {
            $document['name_en'] = $document['name_fi'];
        }

        $types = [
            'library' => 'branchlibrary',
            'centralized_service' => 'unit',
            'facility' => 'library',
            'other' => 'organisation',
        ];

        $subtypes = [
            'library' => 'default',
            'institutional' => 'institution_library',
            'children' => 'childrens_library',
            'music' => 'music_library',
            'special' => 'special_library',
            'vocational_college' => 'vocational_college_library',
            'polytechnic' => 'polytechnic_library',
            'university' => 'university_library',
        ];

        if (isset($types[$document['organisation_type']])) {
            $document['organisation_type'] = $types[$document['organisation_type']];
        }

        if (isset($subtypes[$document['branch_type']])) {
            $document['branch_type'] = $subtypes[$document['branch_type']];
        }

        // Kirjastoauto Espoo ja Kirjastoauto Helsinki
        if (in_array($entity->getId(), [84884, 84791])) {
            $document['organisation_type'] = 'branchlibrary';
            $document['branch_type'] = 'mobile';
            $document['consortium'] = 'helmet';
        }

        $accessibility = [
            null,
            'accessibile_entry',
            'lift',
            'induction_loop',
            'accessible_parking',
            'accessible_toilet',
            'large_typeface_collection',
        ];

        $accessibility_map = [];

        foreach (array_slice($accessibility, 1) as $item) {
            $document['accessiblity'][$item] = false;
        }

        foreach ($entity->getAccessibility() as $item) {
            if (isset($accessibility[$item->getId()])) {
                $document['accessibility'][$accessibility[$item->getId()]] = true;
            }
        }

        return $document;
    }

    protected function mapFields(array & $document, array $map)
    {
        foreach ($map as $from => $to) {
            $document[$to] = array_take($document, $from);
        }
        return $document;
    }

    protected function translated($key, $default_value, array $translations, $dst_key = null)
    {
        if (!$dst_key) {
            $dst_key = $key;
        }
        $langs = ['en', 'ru', 'se', 'sv'];
        $data = [$dst_key . '_fi' => $default_value ?: ""];
        foreach ($langs as $lang) {
            $x = sprintf('%s_%s', $dst_key, $lang);
            if (isset($translations[$lang])) {
                $data[$x] = array_get($translations[$lang], $key, "");
            } else {
                $data[$x] = "";
            }
        }
        return $data;
    }

    protected function mergeServiceTranslations(array $service, array $template)
    {
        foreach ($template as $lang => $values) {
            $template['short_description'] = array_take($template, 'description');
            if (empty($service[$lang])) {
                $service[$lang] = $template;
            } else {
                $service[$lang] = array_merge($service[$lang], array_filter($values));
            }
        }

        return $service;
    }
}
