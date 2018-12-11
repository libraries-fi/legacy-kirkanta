<?php

return [
  // Additional Doctrine configuration that merges with Kirkanta configs.
  'doctrine' => [
    'driver' => [
      'orm_default' => [
        'drivers' => [
          'Kirkanta\Finna\Entity' => 'kirkanta_driver'
        ]
      ],
      'kirkanta_driver' => [
          'paths' => [__DIR__ . '/../src/Entity'],
      ],
    ]
  ]
];
