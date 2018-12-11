<?php

namespace Kirkanta\Form\Fieldset;

use Zend\Form\Fieldset;
use Kirkanta\Entity\PhoneNumber;

class PhoneDetailsFieldset extends Fieldset
{
    public function __construct($name = 'phone', $options = array())
    {
        parent::__construct($name, $options);

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'Name',
            ),
        ));

        $this->add(array(
            'name' => 'number',
            'options' => array(
                'label' => 'Number',
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'options' => array(
                'label' => 'Description',
            ),
        ));

        $this->setObject(new PhoneNumber());
    }
}
