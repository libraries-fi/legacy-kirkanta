<?php

use KirkantaIndexing\Indexer;
use Kirkanta\Helmet\Controller\ServiceTypeController;

return [
  'service_manager' => [
    'factories' => [
      Indexer::class => 'KirkantaIndexing\Indexer::create',
      'Elasticsearch' => 'KirkantaIndexing\Elasticsearch::create',
    ],
  ],
  'router' => [
    'routes' => [
      'indexing' => [
        'type' => 'segment',
        'options' => [
          'route' => '/system/index/:type[/:id]',
          'constraints' => [
            'id' => '\d+'
          ],
          'defaults' => [
            'controller' => 'IndexingController',
            'action' => 'indexEntities',
          ]
        ]
      ],
    ]
  ],
  'controllers' => [
    'factories' => [
      'IndexingController' => 'KirkantaIndexing\Controller\IndexingController::create',
    ]
  ],
  'bjyauthorize' => [
    'guards' => [
      KirkantaIndexing\Authentication\SecretKeyGuard::class => [
        'routes' => [
          'indexing',
        ],
      ]
    ]
  ]
];
