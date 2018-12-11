<?php

namespace Kirkanta\Finna\Event\Listener;

use Kirkanta\Event\Listener\AbstractListenerAggregate;
use Kirkanta\Event\FormEvent;
use Kirkanta\Form\ConsortiumForm;

class ConsortiumEdit extends AbstractListenerAggregate
{
    public function __construct()
    {
        $this->events = [
            [ConsortiumForm::class, 'kirkanta.form.consortium.edit.validate', [$this, 'validateForm']],
        ];
    }

    public function validateForm(FormEvent $event)
    {
        $form = $event->getForm();
        $finna_id = $form->get('finna_data')->get('finna_id')->getValue();

        if (empty($finna_id)) {
            $form->remove('finna_data');
            $form->getObject()->setFinnaData(null);
        }
    }
}
