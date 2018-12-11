<?php

namespace ScheduleGenerator;

use ArrayIterator;
use Countable;
use DateInterval;
use DatePeriod;
use DateTime;
use Iterator;
use stdClass;
use Kirkanta\Entity\Period;

class PeriodRangeIterator implements Iterator
{
    protected $start;
    protected $end;
    protected $periods;
    protected $days;

    public function __construct(DateTime $start, DateTime $end, array $periods)
    {
        $this->start = $start;
        $this->end = $end;
        $this->periods = $periods;
    }

    public function current()
    {
        return current($this->days);
    }

    public function key()
    {
        return key($this->days);
    }

    public function next()
    {
        next($this->days);
    }

    public function rewind()
    {
        $this->init();
    }

    public function valid()
    {
        return key($this->days) !== null;
    }

    protected function init()
    {
        $this->days = [];

        $this->periods = array_filter($this->periods, function($p) {
            if (!$p->getDays()) {
                return false;
            }
            return $p->getValidFrom() < $this->end and (!$p->getValidUntil() or $p->getValidUntil() > $this->start);
        });

        usort($this->periods, function($a, $b) {
            if ($a->isContinuous() ^ $b->isContinuous()) {
                return $a->isContinuous() - $b->isContinuous();
            }

            $diff = $b->getValidFrom()->diff($a->getValidFrom());

            if ($diff->days == 0) {
                return $a->getWeight() - $b->getWeight();
            } else {
                return $diff->days * ($diff->invert ? -1 : 1);
            }
        });

        foreach ($this->periods as $i => $period) {
            if ($period->isContinuous()) {
                $limit = isset($this->periods[$i+1]) ? $this->periods[$i+1]->getValidFrom() : null;
                $limit = $limit ?: $this->end;
            } else {
                $limit = min($period->getValidUntil(), $this->end);
            }
            $start = max($period->getValidFrom(), $this->start);
            $this->iterateUntil($period, $start, $limit);
        }

        ksort($this->days);
    }

    protected function iterateUntil(Period $period, DateTime $from, DateTime $until)
    {
        $source = $period->getDays();
        $rounds = $period->getValidFrom()->diff($until)->days;
        $range = new DatePeriod($period->getValidFrom(), new DateInterval('P1D'), $until);
        $i = $period->getWeight() < 7 ? 0 : $period->getValidFrom()->format('N') - 1;

        foreach ($range as $dt) {
            if ($dt >= $from) {
                $date = $dt->format('Y-m-d');

                if (!isset($this->days[$date])) {
                    $this->days[$date] = $source[$i % count($source)] + [
                        'pid' => $period->getId(),
                        'date' => $date,
                        'day' => $dt->format('l'),
                        'i' => $i,
                    ];
                }
            }

            $i++;
        }
    }
}
