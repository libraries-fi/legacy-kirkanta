<?php

use KirkantaTest\EntityApi\Controller\TestController;

return [
    'router' => [
        'routes' => [
            'entity_api_test' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/test/entityapi',
                    'defaults' => [
                        'controller' => TestController::class,
                        'action' => 'entityApi',
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
