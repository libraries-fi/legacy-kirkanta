<?php

use ScheduleGenerator\DayGenerator;

return [
  'schedules' => [
    'index' => 'libdir_schdules',
  ],
  'service_manager' => [
    'factories' => [
      DayGenerator::class => 'ScheduleGenerator\DayGenerator::create',
    ],
  ],
  'router' => [
    'routes' => [
      'generate_schedules' => [
        'type' => 'literal',
        'options' => [
          'route' => '/system/generate/schedules',
          'defaults' => [
            'controller' => 'ScheduleController',
            'action' => 'schedules',
          ]
        ]
      ],
      'generate_mobilestops' => [
        'type' => 'literal',
        'options' => [
          'route' => '/system/generate/stops',
          'defaults' => [
            'controller' => 'ScheduleController',
            'action' => 'mobilestops',
          ]
        ]
      ],
    ]
  ],
  'controllers' => [
    'factories' => [
      'ScheduleController' => 'ScheduleGenerator\Controller\ScheduleController::create',
    ]
  ]
];
