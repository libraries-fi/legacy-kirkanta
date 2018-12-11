<?php

namespace Kirkanta;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy as EntityProxyInterface;
use Kirkanta\Entity\EntityInterface;
use Kirkanta\Entity\TemplateEntityInterface;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;

/**
 * Quick hack to implement copying and linking template entities.
 */
class EntityCopy
{
    protected $em;

    public static function createInstance(EntityManagerInterface $em)
    {
        return new static($em);
    }

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function copy(TemplateEntityInterface $template, TemplateEntityInterface $entity = null)
    {
        $class = $template instanceof EntityProxyInterface
            ? get_parent_class($template)
            : get_class($template);

        if (is_null($entity)) {
            $entity = new $class;
        }

        $base = $this->getBaseClass($class);
        call_user_func([$this, '_copy' . $base], $template, $entity);

        return $entity;
    }

    public function link(TemplateEntityInterface $template, TemplateEntityInterface $entity)
    {
        $this->copy($template, $entity);
        $entity->setTemplate($template);
        return $entity;
    }

    protected function getBaseClass($class)
    {
        return substr(strrchr($class, '\\'), 1);
    }

    protected function _copyAccessibilityFeature($template, $accessibility)
    {
        $accessibility->setName($template->getName());
        $accessibility->setDescription($template->getDescription());
        $accessibility->setTranslations($template->getTranslations());
    }

    protected function _copyService($template, $service)
    {
        $service->setName($template->getName());
        $service->setType($template->getType());
        $service->setDescription($template->getDescription());
        $service->setShortDescription($template->getShortDescription());
        $service->setPrice($template->getPrice());
        $service->setPicture($template->getPicture());
        $service->setForLoan($template->getForLoan());
        $service->setTranslations($template->getTranslations());
    }

    protected function _copyPeriod($template, $period)
    {
        $period->setName($template->getName());
        $period->setValidFrom($template->getValidFrom());
        $period->setValidUntil($template->getValidUntil());
        $period->setDescription($template->getDescription());
        $period->setContinuous($template->isContinuous());
        $period->setTranslations($template->getTranslations());
        $period->setDays($template->getDays());
    }
}
