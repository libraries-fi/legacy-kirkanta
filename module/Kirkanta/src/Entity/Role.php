<?php

namespace Kirkanta\Entity;

use BjyAuthorize\Acl\HierarchicalRoleInterface;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="roles")
 */
class Role extends Entity implements HierarchicalRoleInterface
{
    const ADMIN = 'admin';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $role_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="children")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="parent")
     */
    protected $children;

    public function __construct($role_id = null)
    {
        $this->role_id = $role_id;
        $this->children = new DoctrineCollection;
    }

    public function __toString()
    {
        return $this->getRoleId();
    }

    public function getRoleId()
    {
        return $this->role_id;
    }

    public function setRoleId($id)
    {
        $this->role_id = $id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($p)
    {
        $this->description = $p;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Role $role = null)
    {
        $this->parent = $role;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getTree()
    {
        $tree = [$this];
        foreach ($this->children as $role) {
            $tree = array_merge($tree, $role->getTree());
        }
        for ($parent = $this->getParent(); $parent; $parent = $parent->getParent()) {
            $tree[] = $parent;
        }
        return $tree;
    }

    public function getRoot()
    {
        return $this->getParent() ? $this->getParent()->getRoot() : $this;
    }
}
