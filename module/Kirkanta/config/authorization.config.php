<?php

return [
  'default_role' => 'guest',
  'authenticated_role' => 'user',
  'standalone_roles' => ['admin'],
  'guards' => [
    Kirkanta\Authentication\Guard\Route::class => [
      /*
       * Rules can be configured in two ways:
       *  1. Using the same syntax as for BjyAuthorize\Guard\Route
       *  2. Router configuration using key '_roles' (array of role IDs)
       *
       * It is intended that router configuration can be used to pass defaults
       * that can be overridden using this configuration here.
       *
       * WARNING: By default all routes require admin role, unless something
       * else is configured. Also, every rote is allowed to admin!
       */

      [
        'route' => 'admin',
        'roles' => ['user'],
      ],
      [
        'route' => 'account',
        'roles' => ['user'],
        'inherit' => true,
      ],
      [
        'route' => 'help',
        'roles' => ['user'],
      ],
      [
        'route' => 'zfcuser',
        'roles' => ['user'],
        'inherit' => true,
      ],
      [
        'route' => 'zfcuser/login',
        'roles' => ['guest'],
      ],
      [
        'route' => 'zfcuser/register',
        'roles' => ['guest'],
      ],
      [
        'route' => 'organisation',
        'roles' => ['user'],
      ],
      [
        'route' => 'organisation/resources',
        'roles' => ['user'],
        'inherit' => true,
      ],
      [
        'route' => 'servicetype/usage',
        'roles' => ['kimppa-admin'],
        'inherit' => true
      ],
      [
        'route' => 'helmet',
        'roles' => ['kimppa-admin'],
        'inherit' => true
      ],
      [
        'route' => 'indexing',
        'roles' => ['guest', 'user']
      ],
      [
        'route' => 'generate_schedules',
        'roles' => ['guest', 'user']
      ],

      [
        'route' => 'consortium',
        'roles' => ['finna'],
        'inherit' => true,
      ],
    ],

    /*
     * Provides additional access control by checking that user has rights
     * to the entity.
     */
    Kirkanta\Authentication\Guard\EntityRoute::class => [
      'routes' => [
        'organisation/resources/delete',
        'organisation/resources/edit',
      ],
    ],

    Kirkanta\Authentication\Guard\EntityAction::class => []
  ],
  'role_providers' => [
    BjyAuthorize\Provider\Role\Config::class => [
      'guest' => [],
      'user' => [],
    ],
    BjyAuthorize\Provider\Role\ObjectRepositoryProvider::class => [
      'role_entity_class' => Kirkanta\Entity\Role::class,
      'object_manager' => 'Doctrine\ORM\EntityManager',
    ],
  ],
  'resource_providers' => [
    Kirkanta\Authentication\Provider\Resource\EntityConfig::class => [],
  ],
  'rule_providers' => [
    BjyAuthorize\Provider\Rule\Config::class => [
      'allow' => [
        [['admin'], null],
        [
          ['user'],
          ['entity.person', 'entity.organisation', 'entity.service', 'entity.period']
        ],
        [['finna'], 'entity.consortium'],
      ],
      'deny' => [
        [['user'], 'entity', 'admin']
      ]
    ],
  ],
];
