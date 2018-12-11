<?php

namespace Kirkanta\Ptv;

use DateTime;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as HttpClient;
use Interop\Container\ContainerInterface;
use Kirkanta\EntityPluginManager;
use Kirkanta\Entity\Organisation;
use Kirkanta\Ptv\Entity\Meta;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\StorageInterface;

class PtvManager
{
    private $entity_manager;
    private $info;
    private $config;
    private $cache;
    private $http;
    private $mappers;

    public static function create(ContainerInterface $container)
    {
        /*
         * NOTE: Suomi.fi auth tokens are valid for 24 hours.
         */

        $cache = StorageFactory::factory([
            'adapter' => [
                'name' => 'apc',
                'options' => ['ttl' => 3600 * 20]
            ]
        ]);

        return new static(
            $container->get('Doctrine\ORM\EntityManager'),
            $container->get('Kirkanta\EntityPluginManager'),
            $cache,
            $container->get('Config')['ptv']
        );
    }

    public function __construct(EntityManagerInterface $entity_manager, EntityPluginManager $info, StorageInterface $cache, array $config)
    {
        $this->entity_manager = $entity_manager;
        $this->info = $info;
        $this->cache = $cache;

        $this->config = $config + [
            'username' => null,
            'password' => null
        ];

        $this->mappers = [
            Organisation::class => new ServiceLocationMapper,
        ];

        $this->map = [
            Organisation::class => 'organisation'
        ];
    }

    public function __get($key)
    {
        if ($key == 'client') {
            $this->client = new HttpClient(['base_uri' => $this->config['api_url']]);
            return $this->client;
        }
    }

    public function authenticate()
    {
        if ($token = $this->cache->getItem('access_token')) {
            return $token;
        } else {
            if (empty($this->config['username']) || empty($this->config['password'])) {
                throw new \Exception('You must configure username and password in order to use the PTV API');
            }
            $data = [
                'form_params' => [
                    'grant_type' => 'password',
                    'scope' => 'dataEventRecords openid',
                    'client_id' => 'ptv_api_client',
                    'client_secret' => 'openapi',
                    'username' => $this->config['username'],
                    'password' => $this->config['password']
                ]
            ];

            $response = $this->client->request('POST', $this->config['auth_url'], $data);

            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody());
                $token = $result->access_token;
                $this->cache->setItem('access_token', $token);

                return $token;
            }

            throw new \Exception('Failed to authenticate with PTV');
        }
    }

    /**
     * NOTE: Remember to flush the Doctrine entity manager afterwards
     * in order to persist changes made to the Meta entity.
     */
    public function sync($entity)
    {
        $mapper = $this->getMapper($entity);
        $meta = $this->getEntityMeta($entity);

        try {
            $document = $mapper->convert($entity, $meta);

            if ($doc_id = $meta->getPtvIdentifier()) {
                $full_doc = $this->updateDocument($doc_id, $document);
                $meta->setLastSync(new DateTime);
            } else {
                $full_doc = $this->createDocument($document);
                $meta->setPtvIdentifier($full_doc->id);
                $meta->setLastSync(new DateTime);
            }

            return $full_doc;
        } catch (ValidationException $e) {
            $meta->setLastLog([
                'status' => 'validation_failed',
                'errors' => $e->getErrors(),
            ]);

            throw $e;
        } catch (Exception $e) {
            $meta->setLastLog([
                'status' => 'unknown_error',
                'errors' => [$e->getMessage()],
            ]);

            $error = new SynchronizationException('Synchronization failed', 1, $e);

            $error->setPtvDocument($document);
            throw $error;
        }
    }

    public function updateDocument($id, array $document)
    {
        $token = $this->authenticate();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $token),
        ];

        $url = sprintf('%s/%s', $document['type'], $id);

        $response = $this->client->request('PUT', $url, [
            'headers' => $headers,
            'json' => $document['document']
        ]);

        $result = json_decode((string)$response->getBody());
        return $result;
    }

    public function createDocument(array $document)
    {
        $token = $this->authenticate();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $token),
        ];

        $response = $this->client->request('POST', $document['type'], [
            'headers' => $headers,
            'json' => $document['document'],
        ]);

        $result = json_decode((string)$response->getBody());
        return $result;
    }

    public function isEntityManaged($entity)
    {
        if ($this->isEntityTypeSupported($entity)) {
            $entry = $this->getEntityMeta($entity);
            return !is_null($entry);
        }

        return false;
    }

    public function getEntityMeta($entity, $create = false)
    {
        $meta = $this->getRepository()->findOneBy([
            'entity_id' => $entity->getId(),
            'entity_type' => $this->getTypeCode($entity)
        ]);

        if (!$meta && $create) {
            $meta = new Meta;
            $meta->setEntityId($entity->getId());
            $meta->setEntityType($this->getTypeCode($entity));
        }

        return $meta;
    }

    public function getMapper($entity_or_class)
    {
        foreach ($this->mappers as $class => $mapper) {
            if (is_a($entity_or_class, $class, true)) {
                return $mapper;
            }
        }
    }

    public function isEntityTypeSupported($entity_or_class)
    {
        return !is_null($this->getTypeCode($entity_or_class));
    }

    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    private function getTypeCode($entity_or_class)
    {
        $mapper = $this->getMapper($entity_or_class);
        return $mapper ? $mapper->getTypeId() : null;
    }

    private function getRepository()
    {
        return $this->entity_manager->getRepository(Meta::class);
    }
}
