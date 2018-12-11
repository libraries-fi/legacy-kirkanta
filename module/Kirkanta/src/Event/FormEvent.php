<?php

namespace Kirkanta\Event;

use Zend\EventManager\Event;
use Zend\Form\FormInterface;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\RequestInterface;

class FormEvent extends Event
{
    const FORM_INIT = 'kirkanta.form.init';
    const FORM_VALIDATE = 'kirkanta.form.validate';
    const FORM_PROCESS = 'kirkanta.form.process';

    protected $form;
    protected $request;
    protected $route_match;

    public function __construct(FormInterface $form, RequestInterface $request, RouteMatch $match)
    {
        $this->form = $form;
        $this->request = $request;
        $this->route_match = $match;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getRouteMatch()
    {
        return $this->route_match;
    }
}
