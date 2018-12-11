<?php

namespace Kirkanta\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Proxy\Proxy as EntityProxyInterface;

use Kirkanta\Entity\City;
use Kirkanta\Entity\Consortium;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\Region;
use Kirkanta\Entity\Service;
use Kirkanta\I18n\ContentLanguages;

class SlugCreator implements EventSubscriber
{
    protected $em;
    protected $cache = [];

    public function __construct()
    {
        $this->languages = ContentLanguages::create();
    }

    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->em = $args->getObjectManager();
        $this->updateSlug($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->em = $args->getObjectManager();
        $this->updateSlug($args->getObject());
    }

    protected function updateSlug($entity)
    {
        if (method_exists($entity, 'isPublished') and !$entity->isPublished()) {
            return;
        }

        if (!method_exists($entity, 'setSlug') or $entity->getSlug()) {
            return;
        }

        $class = $entity instanceof EntityProxyInterface
            ? get_parent_class($entity)
            : get_class($entity);

        $base = $this->getBaseClass($class);

        if (method_exists($this, '_slug' . $base)) {
            call_user_func([$this, '_slug' . $base], $entity);
        } else {
            $this->_slugGeneric($entity);
        }
    }

    protected function slugify($name, $maxlen = 30)
    {
        if (!strlen($name)) {
            return null;
        }
        $slug = strtolower($name);
        $slug = str_replace(['ä', 'ö', 'å'], ['a', 'o', 'a'], $slug);
        $slug = preg_replace('/[^0-9a-z-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, $maxlen);
    }

    protected function getBaseClass($class)
    {
        return substr(strrchr($class, '\\'), 1);
    }

    protected function _slugGeneric($entity, $maxlen = 30)
    {
        $slug = $this->slugify($entity->getName(), $maxlen);
        $entity->setSlug($slug);

        foreach ($this->languages->getLocales() as $lang)
        {
            if ($lang != $this->languages->getDefaultLocale()) {
                $slug = $this->slugify($entity->getTranslatedValue($lang, 'name'));
                $entity->setTranslatedValue($lang, 'slug', $slug);
            }
        }
    }

    protected function _slugOrganisation(Organisation $organisation)
    {
        $slug = $this->slugify($organisation->getName());
        $suffix = '';
        $include_city = false;
        $maxlen = 120;

        if ($this->slugExists(Organisation::class, $slug)) {
            $include_city = true;
            $slug .= '-' . $organisation->getCity()->getSlug();
        }

        if ($this->slugExists(Organisation::class, $slug)) {
            $slug .= $suffix = $this->randomSuffix();
        }

        $this->cache[Organisation::class][$slug] = true;
        $organisation->setSlug($slug);

        foreach ($this->languages->getLocales() as $lang)
        {
            if ($lang != $this->languages->getDefaultLocale() and $organisation->hasTranslations($lang)) {
                $slug = $this->slugify($organisation->getTranslatedValue($lang, 'name'));

                if ($include_city) {
                    $slug .= '-' . $organisation->getCity()->getTranslatedValue($lang, 'slug');
                }

                $slug = substr($slug, 0, 50 - strlen($suffix)) . $suffix;
                $organisation->setTranslatedValue($lang, 'slug', $slug ?: null);
            }
        }
    }

    protected function _slugService(Service $service)
    {
        $slug = $this->slugify($service->getName());
        $suffix = '';
        if ($this->slugExists(Service::class, $slug)) {
            $suffix = $this->randomSuffix();
            $slug .= $suffix;
        }
        $slug = substr($slug, 0, 50);
        $this->cache[Service::class][$slug] = true;
        $service->setSlug($slug);

        foreach ($this->languages->getLocales() as $lang)
        {
            if ($lang != $this->languages->getDefaultLocale() and $service->hasTranslations($lang)) {
                $slug = $this->slugify($service->getTranslatedValue($lang, 'name'));
                $slug = substr($slug, 0, 50 - strlen($suffix)) . $suffix;
                $service->setTranslatedValue($lang, 'slug', $slug ?: null);
            }
        }
    }

    protected function slugExists($class, $slug)
    {
        if (isset($this->cache[$class][$slug])) {
            return true;
        }
        $match = $this->em->getRepository($class)->findOneBy(['slug' => $slug]);
        return $match != null;
    }

    protected function randomSuffix()
    {
        return '-' . substr(uniqid(true), -5);
    }
}
