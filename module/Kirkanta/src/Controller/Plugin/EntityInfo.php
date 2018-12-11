<?php

namespace Kirkanta\Controller\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy as EntityProxy;
use Kirkanta\EntityUrlBuilder;
use Kirkanta\EntityPluginManager;
use Kirkanta\Service\Url;
use Zend\Form\FormElementManager;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class EntityInfo extends AbstractPlugin
{
    private $info;
    private $em;
    private $entity_class;
    private $entity;

    public static function create(PluginManager $plugins)
    {
        $services = $plugins->getServiceLocator();
        $config = $services->get('Config');

        return new static(
            $services->get('Kirkanta\EntityPluginManager'),
            $services->get('Doctrine\ORM\EntityManager')
        );
    }

    public function __construct(EntityPluginManager $info, EntityManagerInterface $em)
    {
        $this->info = $info;
        $this->em = $em;
    }

    public function __invoke($object_or_class = null)
    {
        if (func_num_args()) {
            $this->bind($object_or_class);
        }
        return $this;
    }

    public function bind($object_or_class)
    {
        if (is_object($object_or_class)) {
            $this->entity = $object_or_class;
            $this->entity_class = $this->entityClass($object_or_class);
        } else {
            $this->entity_class = $object_or_class;
        }
        return $this;
    }

    public function getInfo($key, $default = null)
    {
        $config = $this->config();
        return isset($config[$key]) ? $config[$key] : $default;
    }

    public function form($form_id, array $options = [])
    {
        $form = $this->info->form($this->entity ?: $this->entity_class, $form_id, $options);
        $this->getController()->formEvents()->init($form, $form_id);
        return $form;
    }

    public function idParam()
    {
        return $this->info->idParam($this->entity_class);
    }

    public function route($name)
    {
        return array_get($this->getInfo('routes'), $name);
    }

    public function urlBuilder()
    {
        return $this->info->urlBuilder($this->entity_class);
    }

    public function listBuilder()
    {
        return $this->info->listBuilder($this->entity_class);
    }

    public function repository()
    {
        return $this->em->getRepository($this->entity_class);
    }

    public function config()
    {
        return $this->info->getConfig($this->entity_class);
    }

    /**
     * Works without configuring this instance with an entity class
     */
    public function classForAlias($alias)
    {
        return $this->info->classForAlias($alias);
    }

    /**
     * Works without configuring this instance with an entity class
     */
    public function aliasForClass($class = null)
    {
        return $this->info->aliasForClass($class ?: $this->entity_class);
    }

    protected function entityClass($entity)
    {
        if (is_string($entity)) {
            $class = $entity;
        } else {
            $class = $entity instanceof EntityProxy
                ? get_parent_class($entity)
                : get_class($entity);
        }
        return $class;
    }
}
