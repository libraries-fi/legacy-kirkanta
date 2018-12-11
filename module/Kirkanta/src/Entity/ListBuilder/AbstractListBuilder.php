<?php

namespace Kirkanta\Entity\ListBuilder;

use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Kirkanta\EntityUrlBuilder;
use Kirkanta\Paginator\Adapter\OrmAdapter;
use Kirkanta\EntityPluginManager;
use Kirkanta\Service\Url;
use Samu\Zend\Table\Table;
use Zend\Permissions\Acl\AclInterface as AccessControllerInterface;
use Zend\Mvc\Controller\PluginManager;
use Zend\Paginator\Paginator;
use ZfcUser\Entity\UserInterface;

abstract class AbstractListBuilder
{
    abstract public function getTitle();
    abstract public function build($entities);

    const SORT_COL = 's';
    const SORT_DIR = 'd';

    private $entity_class;
    private $object_manager;
    private $plugins;
    private $url_builder;
    private $query;
    private $filter = [];

    protected $default_sorting = [
        'name' => 'asc',
    ];

    protected $column_map = [];

    public static function createInstance(EntityPluginManager $plugins, $entity_class)
    {
        $sm = $plugins->getServiceLocator();
        return new static(
            $sm->get('Doctrine\ORM\EntityManager'),
            $sm->get('ControllerPluginManager'),
            $plugins->urlBuilder($entity_class),
            $sm->get(Authorize::class),
            $entity_class
        );
    }

    public function __construct(EntityManagerInterface $object_manager, PluginManager $plugins, EntityUrlBuilder $url_builder, Authorize $auth, $entity_class)
    {
        $this->object_manager = $object_manager;
        $this->plugins = $plugins;
        $this->url_builder = $url_builder;
        $this->auth = $auth;
        $this->entity_class = $entity_class;
    }

    public function isAllowed($action)
    {
        $type_id = $this->plugins->get('EntityInfo')->aliasForClass($this->entity_class);
        if ($this->auth->isAllowed('entity', $action)) {
            return true;
        }
        return $this->auth->isAllowed('entity.' . $type_id, $action);
    }

    public function getSorting()
    {
        $col = $this->plugins->get('Params')->fromQuery(self::SORT_COL);
        $dir = $this->plugins->get('Params')->fromQuery(self::SORT_DIR);

        if ($col) {
            return [$col => $dir];
        } else {
            return $this->default_sorting;
        }
    }

    public function getUrlBuilder()
    {
        return $this->url_builder;
    }

    /**
     * Supports simple filters ('field' => 'value').
     *
     * If more complex filters are needed, it's better to compile the query
     * manually and then inject it using setQuery()
     */
    public function setFilter(array $filter)
    {
        $this->filter = $filter;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function load()
    {
        $query = $this->getQuery();
        $adapter = new OrmAdapter($query);
        $pager = new Paginator($adapter);
        $pager->setCurrentPageNumber($this->plugins->get('Params')->fromQuery('page', 1));
        return $pager;
    }

    protected function constructDefaultQuery()
    {
        /*
          NOTE: Searching and sorting translations (stored as JSON objects) requires
          a custom function / parser.

          Proposed syntax:

            SELECT e, TR(name, locale) as HIDDEN x
            FROM Kirkanta\Entity\Foobar
            WHERE TR(name, locale) LIKE 'partial%'
            ORDER BY x

          Parsing it into a partial PostreSQL query:

            TR(name, locale) --> translations->'locale'->>'name'

          NOTE: Obviously this only applies to fields that actually have
          translations. We need access to entity metadata to identify translatable
          fields.

          NOTE: The above function might be generalized into extracting a value
          from the JSON tree from any depth:

            JSON_GET_TEXT(foo, bar, ..., key) --> foo->'bar'->...->>'key'
            JSON_GET_OBJECT(foo, bar, ..., key) --> foo->'bar'->...->'key
            '


          Filtering by multiple groups:

            SELECT u
            FROM users u
            WHERE u.id IN (
                SELECT ur.id
                FROM users_roles ur
                WHERE ur.role_id
                IN (1, 2, 3)
            )
            ORDER BY u.username


         */

        $builder = $this->getObjectManager()->createQueryBuilder()
            ->select('e')
            ->from($this->entity_class, 'e');

        $this->filterQuery($builder, $this->getFilter());
        return $builder;
    }

    public function setQuery(QueryBuilder $builder, array $options = [])
    {
        $options += ['sort' => true, 'filter' => true];
        if (!empty($options['sort'])) {
            $this->sortQuery($builder, $this->getSorting());
        }
        if (!empty($options['filter'])) {
            $this->filterQuery($builder, $this->getFilter());
        }
        $this->query = $builder->getQuery();
    }

    public function getQuery()
    {
        if (!$this->query) {
            $builder = $this->constructDefaultQuery();
            $this->sortQuery($builder, $this->getSorting());
            $this->query = $builder->getQuery();
        }
        return $this->query;
    }

    protected function sortQuery(QueryBuilder $builder, array $sorting, $prefix = 'e')
    {
        foreach ($sorting as $field => $dir) {
            if (isset($this->column_map[$field])) {
                $field = $this->column_map[$field];
            } else {
                $field = 'e.' . $field;
            }
            $builder->addOrderBy($field, $dir);
        }
    }

    protected function filterQuery(QueryBuilder $builder, array $filter, $prefix = 'e')
    {
        $joins = 0;
        foreach ($filter as $field => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    continue;
                }
                if (isset($this->column_map[$field])) {
                    $column = $this->column_map[$field];
                } else {
                    $column = 'e.' . $field;
                }
                $joins++;
                $alias = 'r' . $joins;
                $builder->innerJoin($column, $alias, 'WITH', sprintf('%s.id IN (:%s)', $alias, $field));
                $builder->setParameter($field, $value);
            } else {
                if (strlen($value) == 0) {
                    continue;
                }
                $this->constructFilter($builder, $field, $value);
            }
        }
        return $builder;
    }

    protected function constructFilter(QueryBuilder $builder, $field, $value)
    {
        $metadata = $this->getObjectManager()->getClassMetaData($this->entity_class)->fieldMappings;
        if (isset($this->column_map[$field])) {
            $column = $this->column_map[$field];
        } else {
            $column = 'e.' . $field;
        }
        if (isset($metadata[$field]) and $metadata[$field]['type'] == 'string') {
            $value = '%' . $value . '%';
            $clause = sprintf('LOWER(%s) LIKE LOWER(:%s)', $column, $field);
        } else {
            $clause = sprintf('%s = :%s', $column, $field);
        }
        $builder->andWhere($clause);
        $builder->setParameter($field, $value);
        return $builder;
    }

    protected function getObjectManager()
    {
        return $this->object_manager;
    }

    protected function plugin($name)
    {
        return $this->plugins->get($name);
    }

    protected function table()
    {
        $table = new Table;
        $table->setClass('table')
            ->setSortable(true)
            ->setUrlPrototype('?s=:column&d=:direction')
            ->setSorting(key($this->getSorting()), current($this->getSorting()));
        return $table;
    }

    protected function tr($string)
    {
        return $this->getTranslator()->translate($string);
    }

    protected function url($link_id, array $params)
    {
        $params['action'] = $link_id;
        return $this->url_builder->getUrl($link_id, $params);

        $route = $this->plugin('EntityLink')->getRoute($this->entity_class, $link_id);
        $plugin = $this->plugin('ViewHelper')->get('Url');
        $route['params']['action'] = $link_id;
        return $plugin($route['route'], $route['params']);
    }

    public function editLink($name, $i, $data)
    {
        $url = $this->url('edit', $data);
        $name = $this->plugin('EscapeHtml')->escape($name);
        return sprintf('<a href="%s">%s</a>', $url, $name);
    }

    public function getTranslator()
    {
        return $this->plugin('Tr')->getTranslator();
    }
}
