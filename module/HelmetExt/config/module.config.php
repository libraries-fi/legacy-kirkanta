<?php

use Kirkanta\Entity\ServiceType;
use Kirkanta\Helmet\Controller\ServiceTypeController;
use Kirkanta\Helmet\Form\ServiceTypeHelmetForm;

return [
  'router' => [
    'routes' => [
      'helmet' => [
        'type' => 'literal',
        'may_terminate' => false,
        'options' => [
          'route' => '/helmet',
        ],
        'child_routes' => [
          'servicetype' => [
            'type' => 'literal',
            'may_terminate' => true,
            'options' => [
              'route' => '/servicetype',
              'defaults' => [
                'controller' => ServiceTypeController::class,
                'action' => 'list',
              ]
            ],
            'child_routes' => [
              'edit' => [
                'type' => 'segment',
                'options' => [
                  'route' => '/:id',
                  'defaults' => [
                    'action' => 'edit'
                  ]
                ],
              ]
            ]
          ]
        ]
      ]

    ]
  ],
  'entities' => [
    ServiceType::class => [
      'forms' => [
        'helmet' => ServiceTypeHelmetForm::class
      ]
    ]
  ]
];
