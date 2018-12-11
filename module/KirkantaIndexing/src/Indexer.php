<?php

namespace KirkantaIndexing;

use DateTime;
use Exception;
use ReflectionClass;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Kirkanta\Hydrator\ProperDoctrineObject;
use Kirkanta\I18n\ContentLanguages;
use KirkantaIndexing\Annotation\Document;
use KirkantaIndexing\Event\IndexingEvent;
use KirkantaIndexing\Hydrator\AnnotatedHydrator;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventsCapableInterface;
use Zend\I18n\TranslatorInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Indexer implements EventsCapableInterface
{
    use EventManagerAwareTrait;

    protected $em;
    protected $config;
    protected $bulk_mode = false;
    protected $queue = [];

    public static function create(ServiceLocatorInterface $sm)
    {
        return new static(
            $sm->get('Doctrine\ORM\EntityManager'),
            new ContentLanguages($sm->get('MvcTranslator')),
            $sm->get('Config')
        );
    }

    public function __construct(EntityManagerInterface $em, ContentLanguages $langs, array $config)
    {
        $this->em = $em;
        $this->langs = $langs;
        $this->config = $config;
        $this->getEventManager()->attach(IndexingEvent::INDEX, [$this, 'onIndex']);
        $this->getEventManager()->attach(IndexingEvent::REMOVE, [$this, 'onRemove']);
    }

    public function __destruct()
    {
        if ($this->isBulk()) {
            // $this->flush();
        }
    }

    public function __get($key)
    {
        if ($key == 'es') {
            $config = $this->config['elasticsearch'];
            $host = sprintf('%s:%d', $config['host'], $config['port']);
            $this->es = new Client(['hosts' => [$host]]);
            return $this->es;
        }
        if ($key == 'hydrator') {
            $this->hydrator = new AnnotatedHydrator($this->em, $this->langs->getLocales());
            return $this->hydrator;
        }
    }

    public function getIndexName($type)
    {
        return $this->config['elasticsearch']['indices'][$type];
    }

    public function getElasticClient()
    {
        return $this->es;
    }

    public function isBulk()
    {
        return $this->bulk_mode;
    }

    public function beginBulk()
    {
        $this->bulk_mode = true;
        $this->queue = [];
    }

    public function flush()
    {
        if (!$this->isBulk() or !$this->queue) {
            return;
        }

        // $query = ['body' => []];

        foreach ($this->queue as $sub) {
            $query['body'][] = [
                $sub[0] => [
                    '_index' => $sub[1]['index'],
                    '_type' => $sub[1]['type'],
                    '_id' => $sub[1]['id'],
                ],
            ];
            if (isset($sub[1]['body'])) {
                $query['body'][] = $sub[1]['body'];
            }
            // break;
        }

        // printf("BULK %d\n", count($this->queue));
        $this->es->bulk($query);
        $this->queue = null;
    }

    /**
     * Call this function to index an entity.
     *
     * NOTE: Will silently ignore entities that aren't configured to be indexed.
     */
    public function index($entity)
    {
        if (!$this->config['indexing']['enabled']) {
            throw new Exception('Cannot use Indexer when indexing is disabled');
        }

        $doctype = $this->hydrator->getDocumentType($entity);

        if (!$doctype) {
            return;
        }

        $document = $this->extract($entity);
        $id = $entity->getId();

        $event = new IndexingEvent($entity, $document, [
            'type' => $doctype,
            'id' => $id,
            'index' => $this->getIndexName('main'),
        ]);

        $this->getEventManager()->trigger(IndexingEvent::INDEX, $this, $event);
    }

    public function remove($entity)
    {
        // exit('remove');
        if (!$this->config['indexing']['enabled']) {
            throw new Exception('Cannot use Indexer when indexing is disabled');
        }
        $doctype = $this->hydrator->getDocumentType($entity);
        if (!$doctype) {
            return;
        }

        $event = new IndexingEvent($entity, [], [
            'type' => $doctype,
            'id' => $entity->getId(),
            'index' => $this->getIndexName('main'),
        ]);
        $this->getEventManager()->trigger(IndexingEvent::REMOVE, $event);
    }

    /**
     * Bypass entity hydration and index a raw document
     */
    public function indexDocument($doctype, $id, array $document, $index = null)
    {
        if (!$this->config['indexing']['enabled']) {
            throw new Exception('Cannot use Indexer when indexing is disabled');
        }

        $event = new IndexingEvent(null, $document, [
            'type' => $doctype,
            'id' => $id,
            'index' => $index ?: $this->getIndexName('main'),
        ]);
        $this->getEventManager()->trigger(IndexingEvent::INDEX, $this, $event);
    }

    /**
     * Event handler for Index event
     */
    public function onIndex(IndexingEvent $event)
    {
        $query = [
            'index' => $event->meta['index'],
            'type' => $event->meta['type'],
            'id' => $event->meta['id'],
            'body' => $event->document,
        ];

        // printf("%s\n", $query['id']);

        if ($this->isBulk()) {
            $this->queue[] = ['index', $query];
        } else {
            $response = $this->es->index($query);
            // $event->response = $response;
        }
    }

    public function onRemove(IndexingEvent $event)
    {
        $query = [
            'index' => $event->meta['index'],
            'type' => $event->meta['type'],
            'id' => $event->meta['id'],
        ];

        if ($this->isBulk()) {
            $this->queue[] = ['delete', $query];
        } else {
            try {
                $response = $this->es->delete($query);
            } catch (Missing404Exception $e) {
                // pass
            }
            // $event->response = $response;
        }
    }

    protected function extract($entity)
    {
        $document = $this->hydrator->extract($entity);
        return $document;
    }
}
