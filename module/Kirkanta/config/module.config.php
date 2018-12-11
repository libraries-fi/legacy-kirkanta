<?php

return [
  'kirkanta' => [
    'admin_role' => 'admin',
    'pictures' => [
      /*
       * NOTE: Currently these settings are copied into ScaleUploadedPicture
       * filter because there is no convenient way to inject the configuration
       */
      'root' => 'images',
      'sizes' => [
        'small' => [200, 200],
        'medium' => [1000, 1000],
        'large' => [1980, 1980],
        'huge' => [3840, 3840],
      ],
    ],
    'filesystem' => [
      'user_files' => [
        'host' => 'https://kirkanta.kirjastot.fi',
        'root' => 'public/files',
        'web_root' => '/files',
      ]
    ],
  ],
  'translator' => [
    'locale' => 'fi_FI',
    'translation_file_patterns' => [
      [
        'type'   => 'gettext',
        'base_dir' => __DIR__ . '/../gettext',
        'pattern'  => '%s/messages.mo',
      ],
    ],
  ],
  'controller_plugins' => [
    'invokables' => [
      'ContentLanguages' => Kirkanta\Controller\Plugin\ContentLanguages::class,
      'FormEvents' => Kirkanta\Controller\Plugin\FormEvents::class,
      'FormMessages'  => Kirkanta\Controller\Plugin\FormMessages::class,
      'EntityLink'    => Kirkanta\Controller\Plugin\EntityLink::class,
      'ViewModel'     => Kirkanta\Controller\Plugin\ViewModel::class,
    ],
    'factories' => [
        'EntityInfo' => Kirkanta\Controller\Plugin\EntityInfo::class . '::create',
        'EscapeHtml' => Kirkanta\Controller\Plugin\EscapeHtml::class . '::create',
        'Tr' => Kirkanta\Controller\Plugin\Tr::class . '::create',
        'ViewHelper' => Kirkanta\Controller\Plugin\ViewHelper::class . '::create',
    ],
  ],
  'controllers' => [
    'abstract_factories' => [
      Kirkanta\Factory\StaticMethodFactory::class,
    ],
    'invokables' => [
      Kirkanta\Controller\AdminController::class => Kirkanta\Controller\AdminController::class,
      Kirkanta\Controller\SystemController::class => Kirkanta\Controller\SystemController::class,
    ],
  ],
  'filters' => [
    'aliases' => [
      'ScaleUploadedPicture' => Kirkanta\Filter\ScaleUploadedPicture::class,
    ],
    'abstract_factories' => [
      Kirkanta\Factory\StaticMethodFactory::class,
    ],
    'invokables' => [
      'CopyFromField' => 'Kirkanta\Filter\CopyFromField',
      'CopyToField' => 'Kirkanta\Filter\CopyToField',
    ]
  ],
  'form_elements' => [
    'abstract_factories' => [
      /*
       * NOTE: These are executed from last to first.
       */

      Kirkanta\Factory\GenericFormFactory::class,
      Kirkanta\Factory\EntityFormFactory::class,
      Kirkanta\Factory\SearchFormFactory::class,
    ],
    'invokables' => [
      'period_day_collection' => Kirkanta\Form\Element\PeriodDayCollection::class,
    ],
  ],
  'view_helpers' => [
    'invokables' => [
      'Pager' => Kirkanta\View\Helper\Pager::class,
      'AssetUrl'  => Kirkanta\View\Helper\AssetUrl::class,
      'FormatDate' => Kirkanta\View\Helper\FormatDate::class,
      'FormatDateTime'  => Kirkanta\View\Helper\FormatDateTime::class,
      'FormMessages'  => Kirkanta\View\Helper\FormMessages::class,
      'KirkantaFormPicture' => Kirkanta\Form\View\Helper\Picture::class,

      'SamuFormRow' => Kirkanta\I18n\Form\View\Helper\TranslatableFormRow::class,
    ],
    'factories' => [
      'KirkantaPicture' => Kirkanta\View\Helper\Picture::class . '::create',
      'SamuForm' => Kirkanta\I18n\Form\View\Helper\TranslatableForm::class . '::create',
      'SamuFormFieldset' => Kirkanta\I18n\Form\View\Helper\TranslatableFieldset::class . '::create',
    ],
  ],
  'view_manager' => [
    'display_not_found_reason'  => true,
    'display_exceptions'    => true,
    'not_found_template'    => 'error/404',
    'exception_template'    => 'error/index',
    'template_map' => [
      'layout/layout'     => __DIR__ . '/../view/layout/layout.phtml',
      'error/403'       => __DIR__ . '/../view/error/403.phtml',
      'error/404'       => __DIR__ . '/../view/error/404.phtml',
      'error/index'       => __DIR__ . '/../view/error/index.phtml',

      'zfc-user/user/login' => __DIR__ . '/../view/user/login.phtml',
      'zfc-user/user/index' => __DIR__ . '/../view/user/profile.phtml',
      'zfc-user/user/changeemail' => __DIR__ . '/../view/user/change_email.phtml',
      'zfc-user/user/changepassword' => __DIR__ . '/../view/user/change_password.phtml',
    ],
    'template_path_stack' => [
      __DIR__ . '/../view',
    ],
    'strategies' => ['ViewJsonStrategy']
  ],
  'service_manager' => [
    'factories' => [
      'breadcrumbs' => Kirkanta\Navigation\Service\PathCrumbNavigationFactory::class,
      'navigation' => Kirkanta\Navigation\Service\AclNavigationFactory::class,
      'organisation_form_navigation' => Kirkanta\Navigation\Service\OrganisationFormNavigationFactory::class,
      'mobile_library_form_navigation' => Kirkanta\Navigation\Service\MobileLibraryFormNavigationFactory::class,
      'mobile_stop_form_navigation' => Kirkanta\Navigation\Service\MobileStopFormNavigationFactory::class,
      'kirkanta_identity_provider' => Kirkanta\Authentication\AuthenticationIdentityProviderFactory::class,
      'ValidatorManager' => Kirkanta\Validator\ValidatorManagerFactory::class,
    ],
    'abstract_factories' => [
      Kirkanta\Factory\StaticMethodFactory::class,
    ],
    'invokables' => [
      'Imagine' => Imagine\Gd\Imagine::class,
    ]
  ],
  'validators' => [
    'abstract_factories' => [
      Kirkanta\Factory\StaticMethodFactory::class,
    ],
    'aliases' => [
      'UniqueValue' => Kirkanta\Validator\UniqueValue::class,
      'EntityNoRecursion' => Kirkanta\Validator\Entity\NoRecursion::class,
    ]
  ],
  'router' => [
    'routes' => require 'router.config.php',
  ],
  'doctrine' => require 'doctrine.config.php',
  'entities' => require 'entities.config.php',
  'asset_manager'=> require 'assets.config.php',
  'navigation' => require 'navigation.config.php',
  'bjyauthorize' => require 'authorization.config.php',
];
