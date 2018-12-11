  <?php

use ServiceTool\Controller\AdminController;

return [
  'router' => [
    'routes' => [
      'servicetool' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
          'route' => '/admin',
          'defaults' => [
            'controller' => AdminController::class,
            'action' => 'index',
          ]
        ],
        'child_routes' => [
          'select-services' => [
            'type' => 'literal',
            'options' => [
              'route' => '/servicetool',
              'defaults' => [
                'action' => 'selectServices',
              ]
            ]
          ],
          'merge-services' => [
            'type' => 'literal',
            'options' => [
              'route' => '/servicetool/merge',
              'defaults' => [
                'action' => 'mergeServices',
              ]
            ]
          ],
        ]
      ],
    ]
  ],
  'controllers' => [
    // 'factories' => [
    //   'ServiceToolController' => 'ServiceTool\Controller\AdminController::create',
    // ],
  ],
  'view_manager' => [
    'template_path_stack' => [
      __DIR__ . '/../view',
    ],
  ]
];
