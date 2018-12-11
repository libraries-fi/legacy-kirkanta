<?php

namespace Kirkanta\Ptv\Util;

use DateTime;
use Kirkanta\Entity\Period;
use ScheduleGenerator\DayGenerator;

class OpeningTimes
{
    public static function filterInactivePeriods(array $periods)
    {
        $filtered = [];
        $continuous = [];
        $now = new DateTime;

        foreach ($periods as $period) {
            if ($date = $period->getValidUntil()) {
                if ($date > $now) {
                    $filtered[] = $period;
                }
            } else {
                $continuous[] = $period;
            }
        }

        usort($continuous, function($a, $b) {
            $a = $a->getValidFrom();
            $b = $b->getValidFrom();
            if ($a < $b) {
                return -1;
            } else if ($a > $b) {
                return 1;
            } else {
                return 0;
            }
        });

        $filtcont = array_filter($continuous, function($p) use($now) {
            return $p->getValidFrom() > $now;
        });

        if (empty($filtcont)) {
            if (!empty($continuous)) {
                $filtered[] = end($continuous);
            }
        } else {
            $filtered = array_merge($filtered, $continuous);
        }

        usort($filtered, function($a, $b) {
            $a = $a->getValidFrom();
            $b = $b->getValidFrom();
            if ($a == $b) {
                return 0;
            } else {
                return $a->diff($b)->invert == 1;
            }
        });

        return $filtered;
    }

    public static function weekDefinitions(Period $period)
    {
        if (count($period->getDays()) == 7) {
            // Use '12pm' as a quick hack to do away with the problem of generating UNIX timestamps
            // from timezone'd datetime values...
            $start_time = strtotime('Monday 12pm', $period->getValidFrom()->getTimestamp());
            $end_time = strtotime('Sunday 12pm', $start_time);
            // return [];
        } else {
            // Again, add 12 hours to avoid issues caused by combining unixtime with timezones...
            $start_time = strtotime($period->getValidFrom()->format('Y-m-d')) + 3600 * 12;
            $end_time = strtotime($period->getValidUntil()->format('Y-m-d')) + 3600 * 12;
        }

        $from = DateTime::createFromFormat('U', $start_time);
        $to = DateTime::createFromFormat('U', $end_time);

        return DayGenerator::range([$period], $from, $to);
    }
}
