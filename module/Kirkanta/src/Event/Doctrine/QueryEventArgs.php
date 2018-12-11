<?php

namespace Kirkanta\Event\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\QueryBuilder;
use Kirkanta\Entity\User;

class QueryEventArgs extends EventArgs
{
    const preTemplateQuery = 'preTemplateQuery';

    public $query;
    public $entity_class;
    public $current_user;

    public function __construct(QueryBuilder $query, $entity_class, User $user = null)
    {
        $this->query = $query;
        $this->entity_class = $entity_class;
        $this->current_user = $user;
    }
}
