<?php

/*********************************************************************
 *
 * Points to consider
 * - Field wrappers; FormRow
 * - Repetition elements; FormCollection
 *
 *********************************************************************/

/*
 * Options for Form element
 * Fields
 *  - Username
 *  - Password
 */
$form_options = [
    'children' => [
        'username' => [
            'wrap' => 'Foobar\Helper\CustomRow',
            'render' => 'Foobar\Helper\CustomInput',
            'attributes' => [
                'class' => 'foobar',
            ]
        ],
    ]
];
