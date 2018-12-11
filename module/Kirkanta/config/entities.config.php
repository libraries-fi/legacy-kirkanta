<?php

use Kirkanta\Form;

return [
    'Kirkanta\Entity\AccessibilityFeature' => [
        'alias' => 'accessibility',
        'type_label' => _('Accessibility'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\AccessibilityFeatureListBuilder',
        'forms' => [
            'edit' => Form\AccessibilityFeatureForm::class,
            'search' => Form\AccessibilityFeatureSearchForm::class,
            'templates' => Form\TemplateSelectForm::class,
        ],
        'routes' => [
            'list' => 'accessibility',
            'edit' => 'accessibility/edit',
            'delete' => 'accessibility/delete',
        ],
    ],
    'Kirkanta\Entity\City' => [
        'alias' => 'city',
        'type_label' => _('City'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\CityListBuilder',
        'forms' => [
            'edit' => Form\CityForm::class,
            'search' => Form\CitySearchForm::class,
        ],
        'routes' => [
            'list' => 'city',
            'edit' => 'city/edit',
            'delete' => 'city/delete',
        ],
    ],
    'Kirkanta\Entity\Consortium' => [
        'alias' => 'consortium',
        'type_label' => _('Consortium'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\ConsortiumListBuilder',
        'forms' => [
            'edit' => Form\ConsortiumForm::class,
            'search' => Form\AccessibilityFeatureSearchForm::class,
        ],
        'routes' => [
            'list' => 'consortium',
            'edit' => 'consortium/edit',
            'delete' => 'consortium/delete',
        ],
    ],
    'Kirkanta\Entity\Notification' => [
        'alias' => 'notification',
        'type_label' => _('Notification'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\NotificationListBuilder',
        'forms' => [
            'edit' => Form\NotificationForm::class,
        ],
        'routes' => [
            'list' => 'notification',
            'edit' => 'notification/edit',
            'delete' => 'notification/delete',
        ],
    ],
    'Kirkanta\Entity\Organisation' => [
        'alias' => 'organisation',
        'type_label' => _('Organisation'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\OrganisationListBuilder',
        'forms' => [
            'add' => Form\OrganisationAddForm::class,
            'edit' => Form\OrganisationForm::class,
            'search' => Form\OrganisationSearchForm::class,
        ],
        'routes' => [
            'list' => 'organisation',
            'edit' => 'organisation/edit',
            'add' => 'organisation/add',
            'delete' => 'organisation/delete',
        ],
    ],
    'Kirkanta\Entity\Period' => [
        'alias' => 'period',
        'type_label' => _('Period'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\PeriodListBuilder',
        'forms' => [
            'edit' => Form\PeriodForm::class,
            'search' => Form\PeriodSearchForm::class,
            'templates' => Form\TemplateSelectForm::class,
        ],
        'routes' => [
            'list' => 'period',
            'edit' => 'period/edit',
            'delete' => 'period/delete',
        ],
    ],
    'Kirkanta\Entity\Person' => [
        'alias' => 'person',
        'type_label' => _('Person'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\PersonListBuilder',
        'forms' => [
            'edit' => Form\PersonForm::class,
            'search' => Form\PersonSearchForm::class,
        ],
        'routes' => [
            'list' => 'person',
            'edit' => 'person/edit',
            'delete' => 'person/delete',
        ],
    ],
    'Kirkanta\Entity\PhoneNumber' => [
        'alias' => 'phone',
        'type_label' => _('Phone Number'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\PhoneNumberListBuilder',
        'forms' => [
            'edit' => Form\PhoneNumberForm::class,
        ],
        'routes' => [
            'list' => 'phone_number',
            'edit' => 'phone_number/edit',
            'delete' => 'phone_number/delete',
        ],
    ],
    'Kirkanta\Entity\Picture' => [
        'alias' => 'picture',
        'type_label' => _('Picture'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\PictureListBuilder',
        'forms' => [
            'edit' => Form\PictureForm::class,
        ],
        'routes' => [
            'list' => 'picture',
            'edit' => 'picture/edit',
            'delete' => 'picture/delete',
        ],
    ],
    'Kirkanta\Entity\ProvincialLibrary' => [
        'alias' => 'provincial_library',
        'type_label' => _('Provincial Library'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\ProvincialLibraryListBuilder',
        'forms' => [
            'edit' => Form\ProvincialLibraryForm::class,
        ],
        'routes' => [
            'list' => 'provincial_library',
            'edit' => 'provincial_library/edit',
            'delete' => 'provincial_library/delete',
        ],
    ],
    'Kirkanta\Entity\Region' => [
        'alias' => 'region',
        'type_label' => _('Region'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\RegionListBuilder',
        'forms' => [
            'edit' => Form\RegionForm::class,
        ],
        'routes' => [
            'list' => 'region',
            'edit' => 'region/edit',
            'delete' => 'region/delete',
        ],
    ],
    'Kirkanta\Entity\Role' => [
        'alias' => 'role',
        'type_label' => _('User Group'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\RoleListBuilder',
        'forms' => [
            'edit' => Form\RoleForm::class,
            'search' => Form\RoleSearchForm::class,
        ],
        'routes' => [
            'list' => 'role',
            'edit' => 'role/edit',
            'delete' => 'role/delete',
        ],
    ],
    'Kirkanta\Entity\Service' => [
        'alias' => 'service',
        'type_label' => _('Service'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\ServiceListBuilder',
        'forms' => [
            'edit' => Form\ServiceForm::class,
            'search' => Form\ServiceSearchForm::class,
            'templates' => Form\ServiceTemplateSelectForm::class,
        ],
        'routes' => [
            'list' => 'service',
            'edit' => 'service/edit',
            'delete' => 'service/delete',
        ],
    ],
    // 'Kirkanta\Entity\ServiceReference' => [
    //     'alias' => 'service_ref',
    //     'list_builder' => 'Kirkanta\Entity\ListBuilder\ServiceReferenceListBuilder',
    //     'template_class' => 'Kirkanta\Entity\Service',
    //     'forms' => [
    //         'edit' => Form\ServiceReferenceForm::class,
    //         'search' => Form\ServiceSearchForm::class,
    //     ],
    //     'routes' => [],
    // ],
    'Kirkanta\Entity\ServiceType' => [
        'alias' => 'servicetype',
        'type_label' => _('Service Type'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\ServiceTypeListBuilder',
        'forms' => [
            'edit' => Form\ServiceTypeForm::class,
            'search' => Form\ServiceTypeSearchForm::class,
            'templates' => Form\ServiceTemplateSelectForm::class,
        ],
        'routes' => [
            'list' => 'servicetype',
            'edit' => 'servicetype/edit',
            'delete' => 'servicetype/delete',
        ],
    ],
    'Kirkanta\Entity\AccessibilityReference' => [
        'alias' => 'accessibility_ref',
        'template_class' => 'Kirkanta\Entity\AccessibilityFeature',
        'list_builder' => 'Kirkanta\Entity\ListBuilder\AccessibilityReferenceListBuilder',
        'forms' => [
            'edit' => Form\AccessibilityReferenceForm::class,
            'search' => Form\AccessibilityFeatureSearchForm::class,
        ],
        'routes' => [
            'list' => 'accessibility',
        ],
    ],
    'Kirkanta\Entity\User' => [
        'alias' => 'user',
        'type_label' => _('User'),
        'list_builder' => 'Kirkanta\Entity\ListBuilder\UserListBuilder',
        'forms' => [
            'edit' => Form\UserForm::class,
            'search' => Form\UserSearchForm::class,
        ],
        'routes' => [
            'list' => 'user',
            'edit' => 'user/edit',
            'delete' => 'user/delete',
        ],
    ],
    'Kirkanta\Entity\OrganisationWebLink' => [
        'alias' => 'organisation_link',
        'type_label' => _('Website'),
        'list_builder' => Kirkanta\Entity\ListBuilder\OrganisationWebLinkListBuilder::class,
        'forms' => [
            'edit' => Form\OrganisationWebLinkForm::class,
        ],
        'routes' => [
            'list' => 'organisation_link',
            'edit' => 'organisation_link/edit',
            'delete' => 'organisation_link/delete',
        ],
    ],
    'Kirkanta\Entity\OrganisationWebLinkGroup' => [
        'alias' => 'organisation_link_group',
        'type_label' => _('Link Group'),
        'list_builder' => Kirkanta\Entity\ListBuilder\OrganisationWebLinkGroupListBuilder::class,
        'forms' => [
            'edit' => Form\OrganisationWebLinkGroupForm::class,
        ],
        'routes' => [
            'list' => 'organisation_link_group',
            'edit' => 'organisation_link_group/edit',
            'delete' => 'organisation_link_group/delete',
        ],
    ],
];
