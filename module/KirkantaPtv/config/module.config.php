<?php

use Kirkanta\Ptv\Controller\ExportController;
use BjyAuthorize\Provider\Resource\Config as ConfigResource;
use BjyAuthorize\Provider\Rule\Config as ConfigRule;

return [
  'ptv' => require 'ptv.config.php',
  'router' => [
    'routes' => require 'router.config.php'
  ],
  'bjyauthorize' => [
    'resource_providers' => [
      ConfigResource::class => [
        'ptv' => []
      ]
    ],
    'rule_providers' => [
      ConfigRule::class => [
        'allow' => [
          [['vaasa'], 'ptv']
        ]
      ]
    ],
    'guards' => [
      Kirkanta\Authentication\Guard\Route::class => [
        [
          'route' => 'kirkanta_ptv',
          'roles' => ['vaasa'],
          'inherit' => true
        ]
      ]
    ]
  ],
  'doctrine' => [
    'driver' => [
      'orm_default' => [
        'drivers' => [
          'Kirkanta\Ptv\Entity' => 'kirkanta_driver'
        ]
      ],
      'kirkanta_driver' => [
          'paths' => [__DIR__ . '/../src/Entity'],
      ],
    ]
  ],
  'view_manager' => [
    'template_map' => [
      'kirkanta/ptv/status-notification' => __DIR__ . '/../view/status-notification.phtml',
    ],
    'template_path_stack' => [
      __DIR__ . '/../view'
    ]
  ]
];
