<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection as CollectionInterface;

abstract class Entity implements EntityInterface
{
    const STATE_DELETED = -1;
    const STATE_UNPUBLISHED = 0;
    const STATE_PUBLISHED = 1;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function isNew()
    {
        return !$this->getId();
    }

    protected function addItems(CollectionInterface $collection, $items, $inverse_callback = null)
    {
        foreach ($items as $item) {
            if ($item and !$collection->contains($item)) {
                $collection->add($item);

                if ($inverse_callback) {
                    call_user_func([$item, $inverse_callback], $this);
                }
            }
        }
    }

    /**
     * NOTE: Argument passed to $inverse_callback method will depend on the type of $inverse_callback itself.
     *
     * @param $inverse_callback Can be either string (null setter) or a callable (custom handler).
     */
    protected function removeItems(CollectionInterface $collection, $items, $inverse_callback = null)
    {
        foreach ($items as $item) {
            if ($item and $collection->contains($item)) {
                $collection->removeElement($item);

                if (is_string($inverse_callback)) {
                    // This handler is meant to be null setter.
                    call_user_func([$item, $inverse_callback], null);
                } elseif (is_callable($inverse_callback)) {
                    // For custom handling of removal of this entity.
                    $inverse_callback($item);
                }
            }
        }
    }

    protected function propertyToMethod($prop, $get_or_set = 'get')
    {
        $parts = explode('_', $prop);
        $parts = array_map(function($w) { return ucfirst($w); }, $parts);
        $method = $get_or_set . implode('', $parts);

        return $method;
    }

    protected function slugify($string, $append_id = false, $maxlen = 60)
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[\-\s]+/', '_', $slug);
        $slug = preg_replace('/[^a-z0-9]/', '', $slug);

        if ($maxlen > 0) {
            $slug = substr($slug, 0, $maxlen);
        }

        if ($append_id) {
            $id = substr(uniqid(true), -5);

            if ($maxlen) {
                $slug = substr($slug, 0, $maxlen - strlen($id));
            }

            $slug .= $id;
        }

        return $slug;
    }
}
