<?php

namespace Kirkanta;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy as EntityProxy;
use Interop\Container\ContainerInterface;
use Kirkanta\EntityUrlBuilder;
use Kirkanta\Form\EntityForm;
use Kirkanta\Form\EntityDeleteForm;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;
use Kirkanta\Service\Url;
use Zend\Form\FormElementManager;
use Zend\Form\FormInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityPluginManager
{
    private $config;
    private $container;

    public static function create(ContainerInterface $container)
    {
        return new static($container, $container->get('Config')['entities']);
    }

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * This method exists to provide semantic compatibility with ZF2 plugin managers.
     */
    public function getServiceLocator()
    {
        return $this->container;
    }

    public function idParam($class)
    {
        return $this->aliasForClass($class) . '_id';
    }

    public function getConfig($class)
    {
        if (isset($this->config[$class])) {
            return $this->config[$class];
        }
        throw new Exception(sprintf('Invalid entity type \'%s\'', $class));
    }

    public function getOption($class, $key, $default = null)
    {
        $config = $this->getConfig($class);
        return isset($config[$key]) ? $config[$key] : $default;
    }

    public function classForAlias($alias)
    {
        foreach ($this->config as $class => $config) {
            if ($config['alias'] == $alias) {
                return $class;
            }
        }
        throw new Exception(sprintf('Invalid entity alias \'%s\'', $alias));
    }

    public function form($entity_or_class, $form_id, array $options = [])
    {
        if (is_object($entity_or_class)) {
            $entity = $entity_or_class;
            $class = $entity instanceof EntityProxy ? get_parent_class($entity) : get_class($entity);
        } else {
            $class = $entity_or_class;
            $entity = new $class;
        }
        $form_config = $this->getOption($class, 'forms', []) + [
            'delete' => 'Kirkanta\Form\EntityDeleteForm',
        ];

        if (!isset($form_config[$form_id])) {
            throw new InvalidEntityPluginException(sprintf('Invalid form \'%s\' requested for class \'%s\'', $form_id, $class));
        }

        $form = $this->container->get('FormElementManager')->get($form_config[$form_id], $options + [
            'user' => $this->container->get('Zend\Authentication\AuthenticationService')->getIdentity(),
            'entity' => $entity,
            'entity_class' => $class,
        ]);

        $this->initializeForm($form);

        return $form;
    }

    public function aliasForClass($class)
    {
        $config = $this->getConfig($class);
        return $config['alias'];
    }

    public function listBuilder($class, $type = null)
    {
        $builder_class = $this->getOption($class, 'list_builder');
        return $builder_class::createInstance($this, $class);
    }

    public function urlBuilder($class)
    {
        $urls = $this->container->get('Kirkanta\UrlBuilder');
        return new EntityUrlBuilder($this->getConfig($class), $urls);
    }

    private function initializeForm(FormInterface $form)
    {
        if ($form instanceof EntityForm) {
            $hydrator = new DoctrineHydrator($this->entityManager());
            $form->setHydrator($hydrator);
            $form->bind($form->getObject());
            $form->injectActionButtons();
            $form->getEventManager()->trigger('init', new \stdClass);
        }

        if ($form instanceof EntityDeleteForm) {
            $params = $this->container->get('Application')->getMvcEvent()->getRouteMatch()->getParams();
            $proto = $form->getUrlBuilder()->getUrlPrototype('list');
            $proto['params'] += $params;
            $form->getUrlBuilder()->setUrlPrototype('list', $proto['route'], $proto['params']);

            $form->get('submit')->setLabel($form->getTranslator()->translate('Delete'));

            if ($form->getObject() instanceof \Kirkanta\Entity\Organisation) {
                $form->setMessage($form->getTranslator()->translate('You are going to remove this organisation record completely. Do you want to continue?'));
            }
        }
    }

    private function entityManager()
    {
        return $this->container->get('Doctrine\ORM\EntityManager');
    }
}
