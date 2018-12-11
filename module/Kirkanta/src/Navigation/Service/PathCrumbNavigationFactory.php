<?php

namespace Kirkanta\Navigation\Service;

use Interop\Container\ContainerInterface;
use Zend\Navigation\Service\AbstractNavigationFactory;
use Zend\Http\PhpEnvironment\Request;
use Zend\Uri\Http as Uri;

use Kirkanta\Util\OrganisationResources;

class PathCrumbNavigationFactory extends AbstractNavigationFactory
{
    protected $em;

    public function getName()
    {
        return 'pathcrumbs';
    }

    public function getPages(ContainerInterface $container)
    {
        $this->em = $container->get('Doctrine\ORM\EntityManager');
        $router = $container->get('Router');
        $url = $container->get('Request')->getUri()->getPath();
        $parts = explode('/', $url);
        array_shift($parts);

        $pages = [];
        $url = '';

        if (in_array($parts[0], ['system'])) {
            return [];
        }

        foreach ($parts as $level) {
            $url .= '/' . $level;
            $rq = new Request;
            $rq->setUri(new Uri($url));
            $match = $router->match($rq);

            if ($match and $label = $this->labelForLevel($level, $match)) {
                $params = $match->getParams();
                unset($params['_roles']);

                $pages[] = [
                    'label' => $label,
                    'route' => $match->getMatchedRouteName(),
                    'params' => $params,
                ];
            }
        }

        $pages = $this->preparePages($container, $pages);
        return $pages;
    }

    /**
     * NOTE: Very hackish way for deducing crumb names without setting up
     * a complete navigation for the website.
     */
    protected function labelForLevel($level, $match)
    {
        if (ctype_digit($level)) {
            if ($section = $match->getParam('section') and $class = (new OrganisationResources)->classForSection($section)) {
                $entity = $this->em->find($class, $level);
            } elseif ($match->getParam('entity')) {
                $entity = $this->em->find($match->getParam('entity'), $level);
            }

            if (isset($entity)) {
                $methods = ['getName', 'getTitle', '__toString', 'getUsername'];

                foreach ($methods as $method) {
                    if (method_exists($entity, $method)) {
                        return call_user_func([$entity, $method]);
                    }
                }
            }
        } else {
            return str_replace('_', ' ', ucfirst($level));
        }
    }
}
