<?php

namespace Kirkanta\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class AdminController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('organisation');
    }

    public function testIndexAction()
    {

    }
}
