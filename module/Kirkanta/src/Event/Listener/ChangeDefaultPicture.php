<?php

namespace Kirkanta\Event\Listener;

use Kirkanta\Event\FormEvent;
use Kirkanta\Form\PictureForm;
use Kirkanta\Entity\Picture;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * Very hard to do this properly via Doctrine listeners, so we now do it this way.
 */
class ChangeDefaultPicture extends AbstractListenerAggregate
{
    public function __construct() {
        $this->events = [
            [\Zend\Mvc\Controller\AbstractActionController::class, FormEvent::FORM_PROCESS, [$this, 'onFormProcess']],
        ];
    }

    public function onFormProcess(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form instanceof PictureForm) {
            $this->updateDefaultPicture($form->getObject());
        }
    }

    private function updateDefaultPicture(Picture $picture)
    {
        if (!$picture->getOrganisation()) {
            return;
        }

        if (!$picture->isDefault()) {
            if (count($picture->getOrganisation()->getPictures()) == 1) {
                $picture->setDefault(true);
            }
            return;
        }

        foreach ($picture->getOrganisation()->getPictures() as $another) {
            if ($another->getId() != $picture->getId()) {
                $another->setDefault(false);
            }
        }
    }
}
