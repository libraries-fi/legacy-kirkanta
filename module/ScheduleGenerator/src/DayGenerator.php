<?php

namespace ScheduleGenerator;

use ArrayObject;
use DateInterval;
use DatePeriod;
use DateTime;
use stdClass;
use Kirkanta\Entity\Period;
use Kirkanta\I18n\TranslatedContent;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Generates list of opening time info for given date range using provided Periods.
 */
class DayGenerator
{
    // protected $days;

    public static function range(array $periods, DateTime $from, DateTime $to)
    {
        return (new static)->generateRange($periods, $from, $to);
    }

    private function filterPeriods($periods, $FROM, $TO)
    {
        $periods =  array_filter($periods, function($p) use($FROM, $TO) {
            if (!$p->getDays()) {
                return false;
            }
            return $p->getValidFrom() <= $TO and (!$p->getValidUntil() or $p->getValidUntil() >= $FROM);
        });
        return $periods;
    }

    private function sortPeriods($periods)
    {
        /*
         * Sort periods by start date and so that fixed-term periods come first.
         */
        usort($periods, function($a, $b) {
            if ($a->getSection() != $b->getSection()) {
                if ($a->getSection() == 'default') {
                    return -1;
                }

                if ($b->getSection() == 'default') {
                    return 1;
                }
            }

            if ($a->isContinuous() ^ $b->isContinuous()) {
                return $b->isContinuous() - $a->isContinuous();
            }

            $diff = $b->getValidFrom()->diff($a->getValidFrom());

            if ($diff->days == 0) {
                return $a->getWeight() - $b->getWeight();
            } else {
                return $diff->days * ($diff->invert ? -1 : 1);
            }
        });
        return $periods;
    }

    private function periodsBySection(array $periods)
    {
        /*
         * NOTE: Ensure that 'default' will be always first!
         * Makes life easier when combining different sections together.
         */
        $groups = ['default' => []];
        foreach ($periods as $period) {
            $groups[$period->getSection()][] = $period;
        }
        return $groups;
    }

    public function generateRange(array $periods, DateTime $FROM, DateTime $TO, array $meta = [])
    {
        $result = [];
        $groups = $this->periodsBySection($periods);

        foreach ($groups as $section => $periods) {
            $periods = $this->filterPeriods($periods, $FROM, $TO);
            $periods = $this->sortPeriods($periods);
            $days = [];

            foreach ($periods as $i => $period) {
                if ($period->isContinuous()) {
                    if (isset($periods[$i+1]) and $periods[$i+1]->isContinuous()) {
                        $to = min($periods[$i+1]->getValidFrom(), $TO);
                    } else {
                        $to = $TO;
                    }
                } else {
                    $to = min($TO, $period->getValidUntil());
                }
                $from = max($FROM, $period->getValidFrom());
                $days = array_merge($days, $this->iteratePeriod($period, $from, $to, $meta));
            }

            foreach ($days as $date => $day) {
                if ($section == 'default') {
                    unset($day['section']);
                    // Use stdClass to ensure empty data is serialized to JSON as '{}'.
                    $day['sections'] = new stdClass;
                    $result[$date] = $day;
                } else {
                    if (!isset($result[$date]['sections'])) {
                        // There is no base period (default section) for this day so we cannot continue.
                        continue;
                    }
                    unset($day['section'], $day['day'], $day['date'], $day['organisation']);
                    $result[$date]['sections']->{$section} = $day;
                }
            }
        }

        return $result;
    }

    private function iteratePeriod(Period $period, DateTime $from, DateTime $to, array $meta)
    {
        $from = clone $from;
        $to = clone $to;
        $range = new DatePeriod($from, new DateInterval('P1D'), $to->add(new DateInterval('P1D')));
        $source = $period->getDays();
        $days = [];

        // $index = $period->getWeight() < 7 ? 0 : $from->format('N') - 1;
        $index = $period->getValidFrom()->diff($from)->format('%a') % count($period->getDays());


        foreach ($range as $dt) {
            $date = $dt->format('Y-m-d');

            $data = $source[$index % count($source)];

            if (is_object($data)) {
                $data = $data->getArrayCopy();
            }

            $data += [
                'date' => $date,
                'day' => (int)$dt->format('N'),
                'info' => null,
                'times' => [],
                'section' => $period->getSection(),
                'organisation' => $period->getOrganisation()->getId(),
                'period' => $period->getId(),
            ];

            $data = TranslatedContent::mergeTranslations($data) + $meta;

            $times = empty($data['times']) ? [] : array_filter($data['times'], function($time) {
                // Some periods include empty time rows and we want to filter them out.
                return !empty($time['opens']) && !empty($time['closes']);
            });

            if ($period->getSection() == 'default') {
                $data['meta'] = ['period' => $period->getId()];
            } else {
                $data['info'] = ['fi' => null, 'sv' => null, 'en' => null, 'se' => null, 'ru' => null];
            }

            if (empty($times)) {
                $data['times'] = [];
                $data['closed'] = true;
            } else {
                $data['opens'] = reset($times)['opens'];
                $data['closes'] = end($times)['closes'];
                $data['closed'] = false;
            }

            if (!is_array($data['info'])) {
                // Ad hoc fix to an issue caused by migration to Kirkanta V4.
                $data['info'] = ['fi' => $data['info']];
            }

            $days[$date] = $data;

            foreach ($days[$date]['times'] as $i => $time) {
                if ($time instanceof ArrayObject) {
                    $days[$date]['times'][$i] = $time->getArrayCopy();
                }
            }
            $index++;
        }

        return $days;
    }
}
