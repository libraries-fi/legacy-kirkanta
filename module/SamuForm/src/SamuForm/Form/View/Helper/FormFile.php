<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;

class FormFile extends FormInput
{
    protected $validTagAttributes = array(
        'name'           => true,
        'accept'         => true,
        'alt'            => true,
        'autofocus'      => true,
        'dirname'        => true,
        'disabled'       => true,
        'form'           => true,
        'formaction'     => true,
        'formenctype'    => true,
        'formmethod'     => true,
        'formnovalidate' => true,
        'formtarget'     => true,
        'multiple'       => true,
        'placeholder'    => true,
        'required'       => true,
        'size'           => true,
        'step'           => true,
        'type'           => true,
    );
}
