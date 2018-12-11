<?php

namespace ScheduleGenerator;

use DateTime;
use Elasticsearch\Client;
use Kirkanta\Entity\Organisation;
use Zend\ServiceManager\ServiceLocatorInterface;

class LibrarySchedules
{
    private $elastic;
    private $config;

    public static function create(ServiceLocatorInterface $sm)
    {
        return new static($sm->get('Elasticsearch'), $sm->get('Config')['elasticsearch']);
    }

    public function __construct(Client $elastic, array $config)
    {
        $this->elastic = $elastic;
        $this->config = $config;
    }

    public function generate(Organisation $library, DateTime $start, DateTime $end)
    {
        $generator = new DayGenerator;
        $days = $generator->generateRange($library->getPeriods()->toArray(), $start, $end, ['organisation' => $library->getId()]);

        if ($library->getBranchType() == 'mobile') {
            $this->generateMobileLibraryRoute($library, $start, $end, $days);
        }

        return $days;
    }

    protected function generateMobileLibraryRoute(Organisation $library, DateTime $start, DateTime $end, array & $source)
    {
        $id_map = [];

        foreach ($library->getChildren() as $child) {
            if ($child->getType() == 'mobile_stop') {
                $id_map[$child->getId()] = $child;
            }
        }
        if (empty($id_map)) {
            return;
        }

        $result = $this->elastic->search([
            'index' => $this->config['indices']['schedules'],
            'type' => 'opening_time',
            'body' => [
                'size' => 99999999,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['terms' => ['organisation' => array_keys($id_map)]],
                            ['range' => ['date' => [
                                'gte' => $start->format('Y-m-d'),
                                'lte' => $end->format('Y-m-d'),
                            ]]],
                            ['term' => ['closed' => false]],
                        ]
                    ]
                ],
                'sort' => ['date', 'opens']
            ],
        ]);

        foreach ($result['hits']['hits'] as $raw) {
            if ($raw['_source']['closed']) {
                continue;
            }
            $day = $raw['_source'];
            $date = $day['date'];
            $stop = $id_map[$day['organisation']];
            $source[$date]['route'][] = [
                'id' => $day['organisation'],
                'name' => $stop->getTranslatedValues('name'),
                'opens' => $day['opens'],
                'closes' => $day['closes'],
                'closed' => $day['closed'],
                'coordinates' => $stop->getCoordinates(),
                'info' => empty($day['info']) ? null : $day['info'],
            ];
            $source[$day['date']] += [
                'organisation' => $library->getId(),
                'date' => $date,
                'day' => $day['day'],
            ];
        }

        foreach ($source as $date => &$day) {
            unset($day['sections']);

            if (!empty($day['route'])) {
                $day += [
                    'closed' => false,
                    'opens' => reset($day['route'])['opens'],
                    'closes' => end($day['route'])['closes'],
                ];
            } else {
                $day['route'] = [];
            }
        }

        ksort($source);
        return $source;
    }
}
