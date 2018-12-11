<?php

namespace Kirkanta\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @deprecated
 */
class EntityLink extends AbstractPlugin
{
    public function __invoke($entity = null, $link_id = null)
    {
        if (!func_num_args()) {
            return $this;
        } else {
            return $this->getRoute($entity, $link_id);
        }
    }

    public function getRoute($entity, $link_id)
    {
        $entity_class = is_object($entity) ? get_class($entity) : $entity;
        $config = $this->getController()->getServiceLocator()->get('Config')['entities'];
        $config = $config[$entity_class];
        return $config['routes'][$link_id];
    }
}
