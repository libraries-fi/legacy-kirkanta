<?php

namespace Kirkanta\Event\Listener;

use Kirkanta\Event\FormEvent;
use Kirkanta\Form\UserForm;
use Zend\Crypt\Password\Bcrypt;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class ChangeUserPassword implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(\Zend\Mvc\Controller\AbstractActionController::class, FormEvent::FORM_PROCESS, [$this, 'changePassword']);
    }

    public function detach(EventManagerInterface $events)
    {
        $events->getSharedManager()->detach(\Zend\Mvc\Controller\AbstractActionController::class, FormEvent::FORM_PROCESS, [$this, 'changePassword']);
    }

    public function changePassword(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form instanceof UserForm and ($pw = $form->get('auth_password')->getValue())) {
            $bcrypt = new Bcrypt;
            $user = $form->getObject();
            $user->setPassword($bcrypt->create($pw));
        }
    }
}
