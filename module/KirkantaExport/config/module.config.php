<?php

use Kirkanta\Export\Controller\ExportController;

return [
  'controllers' => [
    'factories' => [
      ExportController::class => ExportController::class . '::create'
    ]
  ],
  'router' => [
    'routes' => [
      'kirkanta_export' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
          'route' => '/tools/export',
          'defaults' => [
            'controller' => ExportController::class,
            'action' => 'index',
          ]
        ],
        'child_routes' => [
          'library_addresses' => [
            'type' => 'literal',
            'options' => [
              'route' => '/addresses',
              'defaults' => [
                'action' => 'addresses'
              ]
            ]
          ]
        ]
      ]
    ]
  ],
  'view_manager' => [
    'template_path_stack' => [
      __DIR__ . '/../view',
    ],
  ]
];
