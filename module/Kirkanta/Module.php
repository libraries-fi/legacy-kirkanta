<?php

namespace Kirkanta;

use DateTime;
use BjyAuthorize\Exception\UnAuthorizedException;
use Doctrine\ORM\Tools\SchemaValidator;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Form\FieldsetInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\I18n\Translator as MvcTranslator;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container as SessionData;
use Zend\View\View;
use Zend\View\Helper\PaginationControl;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;

use Kirkanta\Entity\Notification;
use Kirkanta\Event;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;
use Kirkanta\Hydrator\TranslatedEntityHydrator;
use Kirkanta\I18n\Translations;
use Kirkanta\I18n\ContentLanguages;
use Kirkanta\Logging\DoctrineLogger;

require 'functions.php';

class Module
{
    public function init(ModuleManager $manager)
    {
        /*
         * Change ZfcUser route paths from /user to /account to prevent collision
         * with User entity routes.
         */
        $manager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, function($event) {
            $config = $event->getConfigListener()->getMergedConfig(false);
            $config['router']['routes']['zfcuser']['options']['route'] = '/account';
            $event->getConfigListener()->setMergedConfig($config);
        });

        $manager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, function($event) {
            $config = $event->getConfigListener()->getMergedConfig(false);
            $config['service_manager']['factories']['BjyAuthorize\Service\Authorize'] = 'Kirkanta\Authentication\AuthorizeService::create';
            $event->getConfigListener()->setMergedConfig($config);
        });
    }

    public function onBootstrap(MvcEvent $event)
    {
        Paginator::setDefaultItemCountPerPage(20);
        PaginationControl::setDefaultViewPartial('kirkanta/partial/pager.phtml');
        PaginationControl::setDefaultScrollingStyle('sliding');

        $application = $event->getApplication();
        $sm = $application->getServiceManager();

        $sm->get('ViewHelperManager')->setAlias('tr', 'translate');

        if ($ui_lang = (new SessionData('kirkanta'))->ui_language) {
            $sm->get('MvcTranslator')->setLocale($ui_lang);


            // \Zend\Validator\AbstractValidator::setDefaultTranslator($mvc_translator);
            // \Zend\Validator\AbstractValidator::setTranslatorEnabled(true);
        }
        \Kirkanta\I18n\Translations::setLocales((new ContentLanguages($sm->get('MvcTranslator')))->getLocales());

        /*
         * Set Table helper hydrator so that entity objects are hydrated to data
         * arrays automatically.
         */
        \Samu\Zend\Table\Table::setDefaultHydratorFactory(function($class, array $fields) use ($sm) {
            // Tables that generate URLs quite often require ID for the routes.
            $fields = array_merge($fields, ['id']);

            $em = $sm->get('Doctrine\ORM\EntityManager');
            return new TranslatedEntityHydrator($em, 'fi', $fields);
        });

        $sm->get('FormElementManager')->addInitializer(function($element) use($event, $sm) {
            $entities = $sm->get('Doctrine\ORM\EntityManager');

            if ($element instanceof ObjectManagerAwareInterface) {
                $element->setObjectManager($entities);
            }

            if ($element instanceof FieldsetInterface) {
                $element->setHydrator(new DoctrineHydrator($entities));
            }
        });

        /*
         * Set HTML document <title> to match what is displayed on the page.
         */
        $application->getEventManager()->attach(MvcEvent::EVENT_RENDER, function(MvcEvent $event) use($sm) {
            $models = array_merge([$event->getViewModel()], $event->getViewModel()->getChildren());
            $manager = $sm->get('ViewHelperManager');

            $lang = substr((new SessionData('kirkanta'))->ui_language, 0, 2);
            $event->getViewModel()->setVariable('language', $lang);

            foreach ($models as $model) {
                if ($title = $model->getVariable('title')) {
                    return $manager->get('HeadTitle')->prepend($title);
                }
            }
        });

        /*
         * Bubblegum to fill in missing parameters for OrganisationController
         * actions.
         */
        $application->getEventManager()->attach(MvcEvent::EVENT_DISPATCH,
        function(MvcEvent $event) use ($sm) {
            $section = $event->getRouteMatch()->getParam('section');
            $name = $event->getRouteMatch()->getMatchedRouteName();

            if (preg_match('#^organisation/(resources|templates)#', $name)) {
                $resource_class = (new Util\OrganisationResources)->classForSection($section);
                $event->getRouteMatch()->setParam('resource', $resource_class);
            }
        }, 1000);

        /*
         * Redirect unauthenticated users to login page.
         */
        $application->getEventManager()->attach(MvcEvent::EVENT_DISPATCH_ERROR,
        function(MvcEvent $event) use ($sm) {
            $exception = $event->getParam('exception');
            $controller = $event->getParam('controller');
            $auth = $sm->get('Zend\Authentication\AuthenticationService');

            if ($exception instanceof UnAuthorizedException and !$auth->hasIdentity()) {
                $event->stopPropagation();

                $url = $event->getRouter()->assemble([], ['name' => 'zfcuser/login']);
                $response = $event->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                $response->setStatusCode(302);
            }
        });

        /*
         * Inject language selector on login form.
         */
        $application->getEventManager()->getSharedManager()->attach('ZfcUser\Form\Login', 'init',
        function($event) use ($sm) {
            $tr = $sm->get('MvcTranslator');
            $form = $event->getTarget();
            $form->get('identity')->setAttribute('autofocus', true);
            $form->add([
                'type' => 'select',
                'name' => 'uilang',
                'options' => [
                    'label' => $tr->translate('Language'),
                    'value_options' => [
                        'fi_FI' => $tr->translate('Finnish'),
                        'sv_FI' => $tr->translate('Swedish'),
                        'en_US' => $tr->translate('English'),
                    ]
                ],
            ]);

            $form->get('identity')->setLabel($tr->translate('Username'));
            $form->get('credential')->setLabel($tr->translate('Password'));
            $form->get('submit')->setLabel($tr->translate('Login'));
        });

        /*
         * Translate form labels on change email form.
         */
        $application->getEventManager()->getSharedManager()->attach('ZfcUser\Form\ChangeEmail', 'init',
        function($event) use ($sm) {
            $tr = $sm->get('MvcTranslator');
            $form = $event->getTarget();

            $form->get('newIdentity')->setLabel($tr->translate('New email'));
            $form->get('newIdentityVerify')->setLabel($tr->translate('Verify email'));
        });

        /*
         * Translate form labels on change password form.
         */
        $application->getEventManager()->getSharedManager()->attach('ZfcUser\Form\ChangePassword', 'init',
        function($event) use ($sm) {
            $tr = $sm->get('MvcTranslator');
            $form = $event->getTarget();

            $form->get('newCredential')->setLabel($tr->translate('New password'));
            $form->get('newCredentialVerify')->setLabel($tr->translate('Verify password'));
        });

        /*
         * Update user's login time and set UI language.
         */
        $event->getApplication()
            ->getServiceManager()
            ->get('ZfcUser\Authentication\Adapter\AdapterChain')
            ->getEventManager()
            ->attach('authenticate', function(AuthEvent $event) use ($sm) {
                // Can't use User::setLastLogin() because it would change time
                // of last modification, which isn't what we want.
                $sm->get('Doctrine\ORM\EntityManager')
                    ->createQueryBuilder()
                    ->update('Kirkanta\Entity\User', 'u')
                    ->set('u.last_login', 'CURRENT_TIMESTAMP()')
                    ->where('u.id = ?0')
                    ->getQuery()
                    ->execute([$event->getParam('identity')]);

                $uilang = $sm->get('zfcuser_login_form')->get('uilang')->getValue();

                $data = new SessionData('kirkanta');
                $data->ui_language = $uilang;
            });

        $this->attachDoctrineSubscribers($sm);
        $this->attachFormListeners($event);
        $this->enableDebugStuff($sm);
    }

    protected function enableDebugStuff(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('Config');

        if (PROD_ENV == false) {
            $em = $sm->get('Doctrine\ORM\EntityManager');

            try {
                $validator = new SchemaValidator($em);
                $errors = $validator->validateMapping();

                if (count($errors)) {
                    print_r($errors);
                }
            } catch (\Exception $e) {
                print $e->getMessage();
                exit;
            }

            if (!empty($config['debug']['enabled'])) {
                $log_file = isset($config['debug']['logfile']) ? $config['debug']['logfile'] : null;
                $logger = new DoctrineLogger();
                $logger->setFile($log_file);

                $em->getConnection()->getConfiguration()->setSQLLogger($logger);
            }
        }
    }

    private function attachDoctrineSubscribers(ServiceLocatorInterface $sm)
    {

        $em = $sm->get('Doctrine\ORM\EntityManager');
        $events = $em->getEventManager();

        $subscribers = [
            new Event\Subscriber\ModifyTemplateEntityQuery,
            new Event\Subscriber\SlugCreator,
            new Event\Subscriber\DefaultEntityWeight,
            new Event\Subscriber\EntityModifiedSubscriber,
            new Doctrine\Subscriber\CreateOrganisationLinkGroups,
        ];

        array_map([$events, 'addEventSubscriber'], $subscribers);
    }

    private function attachFormListeners(MvcEvent $event)
    {
        $services = $event->getApplication()->getServiceManager();


        $listeners = [
            new Event\Listener\ChangeUserPassword,
            new Event\Listener\ChangeDefaultPicture,
            new Event\Listener\InjectNavigation,
            new Event\Listener\InjectNotifications,
            Event\Listener\OrganisationEditAction::create($services),
            Event\Listener\InjectObjectIntoRoute::create($services),
            Event\Listener\InjectConsortiumTabs::create($services),
        ];

        array_map([$event->getApplication()->getEventManager(), 'attach'], $listeners);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'Kirkanta' => __DIR__ . '/src',
                ],
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'Zend\Authentication\AuthenticationService' => function($serviceManager) {
                    return $serviceManager->get('doctrine.authenticationservice.orm_default');
                },
                'EntityManagerAwareInitializer' => function($instance, $service_locator) {
                    if ($instance instanceof ObjectManagerAwareInterface) {
                        $instance->setObjectManager($service_locator->get('Doctrine\ORM\EntityManager'));
                    }
                }
            ]
        ];
    }

    public function getFormElementConfig()
    {
        return [
            'initializers' => [
                'ObjectManagerAwareInterface' => function($element, $sm) {
                    if ($element instanceof \Zend\Form\Element and method_exists($element, 'getProxy')) {
                        if ($sm instanceof AbstractPluginManager) {
                            $sm = $sm->getServiceLocator();
                        }
                        $om = $sm->get('Doctrine\ORM\EntityManager');
                        $element->getProxy()->setObjectManager($om);
                    }
                },
                'TranslatorAwareInterface' => function($element, $sm) {
                    if ($sm instanceof AbstractPluginManager) {
                        $sm = $sm->getServiceLocator();
                    }
                    if ($element instanceof \Zend\I18n\Translator\TranslatorAwareInterface) {
                        $translator = $sm->get('MvcTranslator');
                        $element->setTranslator($translator);
                    }
                },
            ],

        ];
    }
}
