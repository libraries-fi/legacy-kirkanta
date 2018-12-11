<?php

namespace Kirkanta\Ptv\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ptv_meta")
 */
class Meta
{
    const STATE_DRAFT = 0;
    const STATE_PUBLISHED = 1;

    const METHOD_MANUAL = 0;
    const METHOD_AUTOMATIC = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $entity_id;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $entity_type;

    /**
     * @ORM\Column(type="string")
     */
    private $ptv_identifier;

    /**
     * Enable or disable synchronization.
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    /**
     * Record state on PTV's side.
     *
     * @ORM\Column(type="boolean")
     */
    private $published = true;

    /**
     * Policy for pushing updates.
     *
     * @ORM\Column(type="integer")
     */
    private $method = self::METHOD_MANUAL;

    /**
     * @ORM\Column(type="datetime")
     */
    private $last_sync;

    /**
     * @ORM\Column(type="array")
     */
    private $last_log;

    public function getEntityId()
    {
        return $this->entity_id;
    }

    public function setEntityId($id)
    {
        $this->entity_id = (int)$id;
    }

    public function getEntityType()
    {
        return $this->entity_type;
    }

    public function setEntityType($type)
    {
        $this->entity_type = $type;
    }

    public function getPtvIdentifier()
    {
        return $this->ptv_identifier;
    }

    public function setPtvIdentifier($id)
    {
        $this->ptv_identifier = $id;
    }

    public function getLastSync()
    {
        return $this->last_sync;
    }

    public function setLastSync(DateTime $time)
    {
        $this->last_sync = $time;
    }

    public function getLastLog()
    {
        return $this->last_log;
    }

    public function setLastLog($data)
    {
        $this->last_log = $data;
    }

    public function isPublished()
    {
        return $this->published == true;
    }

    public function getPublished()
    {
        return $this->isPublished();
    }

    public function setPublished($state)
    {
        $this->published = (bool)$state;
    }

    public function isEnabled()
    {
        return $this->enabled == true;
    }

    public function getEnabled()
    {
        return $this->isEnabled();
    }

    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;
    }

    public function getState()
    {
        return $this->published ? self::STATE_PUBLISHED : self::STATE_DRAFT;
    }

    public function setState($state)
    {
        $this->published = (bool)$state;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method) {
        $this->method = (int)$method;
    }
}
