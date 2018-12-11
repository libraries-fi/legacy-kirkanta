<?php

namespace Kirkanta\Form;

use Zend\Form\Element\Text;
use Zend\Form\Element\Password;

class LoginForm extends Form {
    public function __construct($id = 'login-form') {
        parent::__construct($id);

        $this->add(array(
            'name' => 'identity',
            'options' => array(
                'label' => 'Username',
            ),
        ));

        $this->add(array(
            'name' => 'credential',
            'type' => 'password',
            'options' => array(
                'label' => 'Password',
            ),
        ));

        $this->add(array(
            'name' => 'login',
            'type' => 'button',
            'options' => array(
                'label' => 'Login',
            ),
            'attributes' => array(
                'value' => 'Login',
                'type' => 'submit',
            ),
        ));
    }
}
