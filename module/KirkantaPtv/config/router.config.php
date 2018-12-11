<?php

use Kirkanta\Ptv\Controller\ExportController;
use Kirkanta\Ptv\Controller\PtvController;

return [
  'kirkanta_ptv' => [
    'type' => 'literal',
    'options' => [
      'route' => '/ptv',
      'defaults' => [
        'controller' => Kirkanta\Ptv\Controller\ExportController::class,
        'action' => 'demo'
      ]
    ],
    'child_routes' => [
      'demo' => [
        'type' => 'literal',
        'options' => [
          'route' => '/demo',
          'defaults' => [
            'controller' => Kirkanta\Ptv\Controller\ExportController::class,
            'action' => 'demo'
          ]
        ]
      ],
      'export' => [
        'type' => 'literal',
        'options' => [
          'route' => '/export',
          'defaults' => [
            'controller' => Kirkanta\Ptv\Controller\ExportController::class,
            'action' => 'export'
          ]
        ]
      ],
      'synchronize' => [
        'type' => 'segment',
        'may_terminate' => true,
        'options' => [
          'route' => '/:type/:id/sync',
          'defaults' => [
            'controller' => Kirkanta\Ptv\Controller\PtvController::class,
            'action' => 'synchronize',
          ]
        ],
        'child_routes' => [
          'run' => [
            'type' => 'literal',
            'options' => [
              'route' => '/run',
              'defaults' => [
                'action' => 'runSynchronization'
              ]
            ]
          ]
        ]
      ],
      'configure' => [
        'type' => 'segment',
        'options' => [
          'route' => '/:type/:id/configure',
          'defaults' => [
            'controller' => Kirkanta\Ptv\Controller\PtvController::class,
            'action' => 'configure',
          ]
        ]
      ],
      'validate' => [
        'type' => 'segment',
        'options' => [
          'route' => '/:type/:id/validate',
          'defaults' => [
            'controller' => Kirkanta\Ptv\Controller\PtvController::class,
            'action' => 'validate',
          ]
        ]
      ]
    ]
  ]
];
