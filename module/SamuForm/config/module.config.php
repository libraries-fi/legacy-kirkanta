<?php

namespace SamuForm;

return [
    'filters' => [
        'factories' => [
            'ArrayCollection' => 'SamuForm\Filter\ArrayCollection::create',
        ]
    ],
    'view_helpers' => [
        'factories' => [
            'samu_form'                 => 'SamuForm\Form\View\Helper\Form::create',
            'samu_form_collection'      => 'SamuForm\Form\View\Helper\FormCollection::create',
            'samu_form_fieldset'        => 'SamuForm\Form\View\Helper\FormFieldset::create',
        ],
        'invokables' => [
            'samu_form_actions'         => 'SamuForm\Form\View\Helper\FormActions',
            'samu_form_button'          => 'SamuForm\Form\View\Helper\FormButton',
            'samu_form_checkbox'        => 'SamuForm\Form\View\Helper\FormCheckbox',
            'samu_form_complex_input'   => 'SamuForm\Form\View\Helper\FormComplexInput',
            'samu_form_element'         => 'SamuForm\Form\View\Helper\FormElement',
            'samu_form_info_text'       => 'SamuForm\Form\View\Helper\FormInfoText',
            'samu_form_input'           => 'SamuForm\Form\View\Helper\FormInput',
            'samu_form_file'            => 'SamuForm\Form\View\Helper\FormFile',
            'samu_form_radio'           => 'SamuForm\Form\View\Helper\FormRadio',
            'samu_form_row'             => 'SamuForm\Form\View\Helper\FormRow',
            'samu_form_select'          => 'SamuForm\Form\View\Helper\FormSelect',
            'samu_form_text'            => 'SamuForm\Form\View\Helper\FormText',
            'samu_form_textarea'        => 'SamuForm\Form\View\Helper\FormTextarea',
            // 'samu_form_fieldset'        => 'SamuForm\Form\View\Helper\FormFieldset',

            'samutwb_form_accordion'    => 'SamuForm\Form\View\Helper\Bootstrap\FormAccordion',
        ],
    ],
    'form_elements' => [
        'invokables' => [
            'samu_array_collection' => 'SamuForm\Form\Element\ArrayCollection',
        ],
    ],
];
