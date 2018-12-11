<?php

return [
    'router' => [
        'routes' => [
            /*
             * NOTE: This overwrites the route defined in module ScheduleGenerator
             * as this legacy support also triggers the modern indexer.
             */
            'generate_schedules' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/system/generate/schedules',
                    'defaults' => [
                        'controller' => 'LegacyScheduleController',
                        'action' => 'schedules',
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            'LegacyScheduleController' => 'LegacyIndexing\Controller\LegacyScheduleController::create',
        ]
    ]
];
