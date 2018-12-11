<?php
chdir('../..');
require 'init_autoloader.php';

use Kirkanta\Entity\Period;
use ScheduleGenerator\PeriodRangeIterator2 as PeriodRangeIterator;
use ScheduleGenerator\DayGenerator2 as DayGenerator;

Zend\Mvc\Application::init(require 'config/application.config.php');

$p1 = new Period;
$p2 = new Period;
$p3 = new Period;
$p4 = new Period;
$p5 = new Period;

$p5->setId(5);
$p5->setValidFrom(new DateTime('+1 month'));

$p1->setId(1);
$p1->setValidFrom(new DateTime('2015-08-27'));
$p1->setContinuous(true);
$p1->setDays([
    ['opens' => '11:00', 'closes' => '20:00'],
    ['opens' => '12:00', 'closes' => '20:00'],
    ['opens' => '13:00', 'closes' => '20:00'],
    ['opens' => '14:00', 'closes' => '20:00'],
    ['opens' => '15:00', 'closes' => '20:00'],
    ['opens' => '16:00', 'closes' => '20:00'],
    ['opens' => '17:00', 'closes' => '20:00'],
]);

$p2->setId(2);
$p2->setValidFrom(new DateTime('2015-09-19'));
$p2->setValidUntil(new DateTime('2015-09-20'));
$p2->setDays([
    ['opens' => null, 'closes' => null],
    ['opens' => null, 'closes' => null],
]);

$p3->setId(3);
$p3->setValidFrom(new DateTime('2015-09-18'));
$p3->setContinuous(true);
$p3->setDays([
    ['opens' => '00:00', 'closes' => '01:00'],
    ['opens' => '00:00', 'closes' => '02:00'],
    ['opens' => '00:00', 'closes' => '03:00'],
    ['opens' => '00:00', 'closes' => '04:00'],
    ['opens' => '00:00', 'closes' => '05:00'],
    ['opens' => '00:00', 'closes' => '06:00'],
    ['opens' => '00:00', 'closes' => '07:00'],
]);

$periods = [$p3, $p1, $p5, $p4, $p2];
$time_start = microtime(true);
// $iterator = new PeriodRangeIterator(new DateTime('2015-01-01'), new DateTime('2017-12-31'), $periods);
$iterator = DayGenerator::range($periods, new DateTime('2015-09-01'), new DateTime('+6 months'));

$i = 0;

foreach ($iterator as $i => $row) {
    printf("%s: %s – %s\n", $row['date'], $row['opens'], $row['closes']);
}

printf("count: %d\n", $i);
printf("Took: %.04f s\n", microtime(true) - $time_start);
