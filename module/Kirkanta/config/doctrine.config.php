<?php

return [
  'driver' => [
    'orm_default' => [
      'drivers' => [
        'Kirkanta\Entity' => 'kirkanta_driver',
      ],
    ],
    'kirkanta_driver' => [
      'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
      'cache' => 'array',
      'paths' => [__DIR__ . '/../../Kirkanta/src/Entity'],
    ],
  ],
  'authentication' => [
    'orm_default' => [
      'object_manager' => 'Doctrine\ORM\EntityManager',
      'identity_class' => 'Kirkanta\Entity\User',
      'identity_property' => 'username',
      'credential_property' => 'password',
    ],
  ],
];
