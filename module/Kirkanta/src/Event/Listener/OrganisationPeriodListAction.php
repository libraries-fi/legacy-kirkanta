<?php

namespace Kirkanta\Event\Listener;

use DateTime;
use Kirkanta\Controller\OrganisationController;
use ScheduleGenerator\DayGenerator;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class OrganisationPeriodListAction extends AbstractListenerAggregate
{
    public function __construct()
    {
        $this->events = [
            [OrganisationController::class, MvcEvent::EVENT_DISPATCH, [$this, 'injectPreview'], -1000],
        ];
    }

    public function injectPreview(MvcEvent $event)
    {
        $match = $event->getRouteMatch();

        if ($match->getMatchedRouteName() != 'organisation/resources') {
            return;
        }

        if ($match->getParam('section') != 'periods') {
            return;
        }

        $periods = $event->getResult()->getVariable('organisation')->getPeriods('default')->toArray();
        $start = new DateTime('monday 1 week ago');
        $end = new DateTime('+9 weeks sunday');

        $range = DayGenerator::range($periods, $start, $end);

        $child = new ViewModel;
        $child->setTemplate('kirkanta/partial/schedules-preview');
        $child->setVariables(['range' => $range]);
        $event->getResult()->addChild($child, 'block_top', true);
    }
}
