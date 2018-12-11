<?php

use Kirkanta\Controller\AccountController;
use Kirkanta\Controller\AdminController;
use Kirkanta\Controller\ConsortiumController;
use Kirkanta\Controller\EntityController;
use Kirkanta\Controller\OrganisationController;
use Kirkanta\Controller\ServiceTypeController;
use Kirkanta\Controller\SystemController;

$entity_child_routes = [
    'delete' => [
        'type' => 'segment',
        'options' => [
            'route' => '/:id/delete',
            'defaults' => [
                'action' => 'delete',
            ],
        ],
        'constraints' => [
            'id' => '\d+',
        ]
    ],
    'edit' => [
        'type' => 'segment',
        'options' => [
            'route' => '/:id',
            'defaults' => [
                'action' => 'edit',
            ],
        ],
        'constraints' => [
            'id' => '\d+',
        ]
    ],
    'add' => [
        'type' => 'segment',
        'options' => [
            'route' => '/add',
            'defaults' => [
                'action' => 'edit',
            ],
        ],
    ],
];

return [
    'purge_removed_pictures' => [
      'type' => 'literal',
      'options' => [
        'route' => '/system/purge-removed-pictures',
      ]
    ],
    'tools' => [
        'type' => 'literal',
        'options' => [
            'route' => '/tools',
            'defaults' => [
                'controller' => SystemController::class,
                'action' => 'tools',
            ]
        ]
    ],
    'help' => [
        'type' => 'literal',
        'options' => [
            'route' => '/help',
            'defaults' => [
                'controller' => SystemController::class,
                'action' => 'help',
            ],
        ]
    ],
    'admin' => [
        'type' => 'literal',
        'options' => [
            'route' => '/',
            'defaults' => [
                'controller' => AdminController::class,
                'action' => 'index',
//                 '_roles' => ['user'],
            ]
        ]
    ],
    'test_index' => [
        'type' => 'literal',
        'options' => [
            'route' => '/index',
            'defaults' => [
                'controller' => AdminController::class,
                'action' => 'testIndex',
            ]
        ]
    ],
    'accessibility' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/accessibility',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\AccessibilityFeature::class,
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'city' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/city',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\City::class,
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'consortium' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/consortium',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Consortium::class,
            ],
        ],
        'child_routes' => [
            'edit' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:id[/:tab]',
                    'defaults' => [
                        'action' => 'edit',
                        'tab' => 'main',
                    ],
                ],
                'constraints' => [
                    'id' => '\d+',
                ]
            ],
            'add' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/add',
                    'defaults' => [
                        'action' => 'edit',
                        'tab' => 'main',
                    ],
                ],
            ],
        ] + $entity_child_routes,
    ],
    'account' => [
        'type' => 'literal',
        'options' => [
            /*
             * NOTE: This is an alias to 'zfcuser' route
             */
            'route' => '/account',
            'defaults' => [
                'controller' => AccountController::class,
            ],
        ],
        'child_routes' => [
            'notifications' => [
                'type' => 'literal',
                'may_terminate' => true,
                'options' => [
                    'route' => '/notifications',
                    'defaults' => [
                        'action' => 'notifications',
                    ]
                ],
                'child_routes' => [
                    'read' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:id',
                            'defaults' => [
                                'action' => 'readNotification',
                                'entity' => Kirkanta\Entity\Notification::class,
                            ]
                        ],
                    ]
                ]
            ],
        ]
    ],
    'notification' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/notification',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Notification::class,
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'period' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/period',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Period::class,
                '_roles' => ['user'],
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'person' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/person',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Person::class,
                '_roles' => ['user'],
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'picture' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/picture',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Picture::class,
                '_roles' => ['user'],
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'provincial_library' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/provincial_library',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\ProvincialLibrary::class,
                // '_roles' => ['admin'],
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'region' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/region',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Region::class,
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'role' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/role',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Role::class,
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'service' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/service',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Service::class,
                '_roles' => ['user'],
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'servicetype' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/servicetype',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\ServiceType::class,
            ],
        ],
        'child_routes' => $entity_child_routes + [
            'usage' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:id/usage',
                    'defaults' => [
                        'controller' => ServiceTypeController::class,
                        'action' => 'usage',
                    ]
                ],
                'constraints' => [
                    'id' => '\d+'
                ]
            ]
        ],
    ],
    'user' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/user',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\User::class,
            ],
        ],
        'child_routes' => $entity_child_routes,
    ],
    'organisation' => [
        'type' => 'literal',
        'may_terminate' => true,
        'options' => [
            'route' => '/organisation',
            'defaults' => [
                'controller' => EntityController::class,
                'action' => 'list',
                'entity' => Kirkanta\Entity\Organisation::class,
                '_roles' => ['user'],
            ],
        ],
        'child_routes' => [
            'add' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/add',
                    'defaults' => [
                        'controller' => OrganisationController::class,
                        'action' => 'add',
                    ]
                ],
            ],
            'mobilestops' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:organisation_id/mobilestops',
                    'defaults' => [
                        'controller' => OrganisationController::class,
                        'action' => 'mobilestops',
                    ],
                    'constraints' => [
                        'organisation_id' => '\d+',
                    ],
                ]
            ],
            'edit' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:organisation_id[/:section]',
                    'defaults' => [
                        'action' => 'edit',
                        'section' => 'basics',
                    ],
                    'constraints' => [
                        'organisation_id' => '\d+',
                        'section' => 'addresses|basics|custom_data|description|misc|transit',
                    ]
                ],
            ],
            'delete' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:organisation_id/delete',
                    'defaults' => [
                        'action' => 'delete',
                    ],
                ],
                'constraints' => [
                    'organisation_id' => '\d+',
                ]
            ],
            'resources' => [
                'type' => 'segment',
                'may_terminate' => true,
                'options' => [
                    'route' => '/:organisation_id/:section',
                    'constraints' => [
                        'organisation_id' => '\d+',
                        'section' => 'accessibility|link_groups|links|pictures|periods|persons|phone_numbers|services',
                    ],
                    'defaults' => [
                        'controller' => OrganisationController::class,
                        'action' => 'listResources',
                    ]
                ],
                'child_routes' => [
                    'add' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'editResource',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:id',
                            'constraints' => [
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                'id' => 'add',
                                'action' => 'editResource',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:id/delete',
                            'constraints' => [
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'deleteResource',
                            ],
                        ],
                    ],
                    'tablesort' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/tablesort',
                            'defaults' => [
                                'action' => 'tableSort'
                            ]
                        ]
                    ]
                ],
            ],
            'templates' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:organisation_id/:section/templates',
                    'constraints' => [
                        'organisation_id' => '\d+',
                        'section' => 'accessibility|periods|persons|services',
                    ],
                    'defaults' => [
                        'controller' => OrganisationController::class,
                        'action' => 'templates',
                    ]
                ],
            ],
            'copy_period' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:organisation_id/periods/copy',
                    'constraints' => [
                        'organisation_id' => '\d+',
                    ],
                    'defaults' => [
                        'controller' => OrganisationController::class,
                        'section' => 'periods',
                        'action' => 'copyPeriod',
                    ]
                ]
            ],
            'copy_service' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:organisation_id/services/copy',
                    'constraints' => [
                        'organisation_id' => '\d+',
                    ],
                    'defaults' => [
                        'controller' => OrganisationController::class,
                        'section' => 'services',
                        'action' => 'copyService',
                    ]
                ]
            ],
        ],
    ],
];
