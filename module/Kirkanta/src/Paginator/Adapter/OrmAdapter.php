<?php

namespace Kirkanta\Paginator\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class OrmAdapter implements AdapterInterface {

    private $query;
    private $paginator;

    public function __construct($query = null) {
        $this->query = $query;
    }

    public function getItems($offset, $page_size) {
        if (!$this->query) {
            return [];
        }
        $this->query->setFirstResult($offset);
        $this->query->setMaxResults($page_size);
        return $this->query->getResult();
    }

    public function count() {
        if (!$this->paginator) {
            $this->paginator = new DoctrinePaginator($this->query);
        }
        return count($this->paginator);
    }
}
