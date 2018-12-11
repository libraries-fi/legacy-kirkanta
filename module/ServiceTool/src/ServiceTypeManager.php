<?php

namespace ServiceTool;

use Doctrine\ORM\EntityManagerInterface;
use Kirkanta\Entity\Service;
use Kirkanta\Entity\ServiceType;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceTypeManager
{
    private $em;

    public static function create(ServiceLocatorInterface $services)
    {
        return new static($services->get('Doctrine\ORM\EntityManager'));
    }

    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->em = $entity_manager;
    }

    public function mergeInto(ServiceType $base, array $others, array $overrides = [])
    {
        /*
         * Cannot use entity queries here as we need to trigger the indexing listeners
         * when we modify and delete entities.
         */

        $old_ids = array_map(function($s) { return $s->getId(); }, $others);
        $result = $this->em->getRepository(Service::class)->findBy(['template' => $old_ids]);

        foreach ($result as $service) {
            $this->processNameOverrides($service, $overrides);
            $service->setTemplate($base);
        }

        $result = $this->em->getRepository(ServiceType::class)->findById($old_ids);

        foreach ($result as $template) {
            $this->em->remove($template);
        }

        $this->em->flush();
    }

    private function processNameOverrides(Service $service, array $overrides)
    {
        if (isset($overrides[$service->getTemplate()->getId()])) {
            $data = $overrides[$service->getTemplate()->getId()];

            if (!$service->getName()) {
                $service->setName($data['name']);
            }

            foreach ($data['translations'] as $lang => $value) {
                if (!$service->getTranslatedValue($lang, 'name')) {
                    $service->setTranslatedValue($lang, 'name', $value);
                }
            }
        }
    }
}
