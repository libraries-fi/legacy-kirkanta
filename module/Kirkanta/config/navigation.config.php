<?php

// This is here to make this word visible to the translator
_('Frontpage');
_('Periods');

return [
  'default' => [
    [
      'label' => _('Accessibility'),
      'route' => 'accessibility',
    ],
    [
      'label' => _('Cities'),
      'route' => 'city',
    ],
    [
      'label' => _('Consortiums'),
      'route' => 'consortium',
    ],
    [
      'label' => _('Notifications'),
      'route' => 'notification',
    ],
    [
      'label' => _('Organisations'),
      'route' => 'organisation',
    ],
    [
      'label' => _('Personnel'),
      'route' => 'person',
    ],
    [
      'label' => _('Provincial Libraries'),
      'route' => 'provincial_library',
    ],
    [
      'label' => _('Regions'),
      'route' => 'region',
    ],
    [
      'label' => _('Schedule Templates'),
      'route' => 'period',
    ],
    [
      'label' => _('Service Templates'),
      'route' => 'service',
    ],
    [
      'label' => _('Service Types'),
      'route' => 'servicetype',
    ],
    [
      'label' => _('Service Types (H)'),
      'route' => 'helmet/servicetype',
    ],
    [
      'label' => _('Users'),
      'route' => 'user',
    ],
    [
      'label' => _('User Groups'),
      'route' => 'role',
    ],
  ],
  'organisation' => [
    [
      'label' => _('« Frontpage'),
      'route' => 'organisation',
    ],
    [
      'label' => _('Basic Details'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'basics'],
    ],
    [
      'label' => _('Description'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'description'],
    ],
    [
      'label' => _('Addresses'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'addresses'],
    ],
    [
      'label' => _('Phone numbers'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'phone_numbers'],
    ],
    [
      'label' => _('Transit directions'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'transit'],
    ],
    [
      'label' => _('Miscellaneous'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'misc'],
    ],
    [
      'label' => _('Schedules'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'periods'],
    ],
    [
      'label' => _('Pictures'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'pictures'],
    ],
    [
      'label' => _('Services'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'services'],
    ],
    [
      'label' => _('Accessibility'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'accessibility'],
    ],
    [
      'label' => _('Personnel'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'persons'],
    ],
    [
      'label' => _('Websites'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'links'],
    ],
    [
      'label' => _('Custom data'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'custom_data'],
    ],
    [
      'label' => _('Delete'),
      'route' => 'organisation/delete',
    ]
  ],
  'mobile_library' => [
    [
      'label' => _('« Frontpage'),
      'route' => 'organisation',
    ],
    [
      'label' => _('Basic Details'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'basics'],
    ],
    [
      'label' => _('Description'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'description'],
    ],
    [
      'label' => _('Addresses'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'addresses'],
    ],
    [
      'label' => _('Phone numbers'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'phone_numbers'],
    ],
    [
      'label' => _('Miscellaneous'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'misc'],
    ],
    [
      'label' => _('Schedules'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'periods'],
    ],
    [
      'label' => _('Pictures'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'pictures'],
    ],
    [
      'label' => _('Services'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'services'],
    ],
    [
      'label' => _('Accessibility'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'accessibility'],
    ],
    [
      'label' => _('Mobile stops'),
      'route' => 'organisation/mobilestops',
      'params' => ['section' => 'description'],
    ],
    [
      'label' => _('Delete'),
      'route' => 'organisation/delete',
    ]
  ],
  'mobile_stop' => [
    [
      'label' => _('« Frontpage'),
      'route' => 'organisation',
    ],
    [
      'label' => _('Basic Details'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'basics'],
    ],
    [
      'label' => _('Addresses'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'addresses'],
    ],
    [
      'label' => _('Schedules'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'periods'],
    ],
    [
      'label' => _('Pictures'),
      'route' => 'organisation/resources',
      'params' => ['section' => 'pictures'],
    ],
    [
      'label' => _('Description'),
      'route' => 'organisation/edit',
      'params' => ['section' => 'description'],
    ],
    [
      'label' => _('Delete'),
      'route' => 'organisation/delete',
    ]
  ],
];
