<?php

namespace Kirkanta\Entity;

use DateTime;
use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as Form;
use ZfcUser\Entity\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends Entity implements ModifiedAwareInterface, ProviderInterface, UserInterface
{
    use ModifiedAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Form\Type("text")
     * @Form\Required({"required": "true"})
     * @Form\Options({"label": "Username"})
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     *
     * @Form\Type("text")
     * @Form\Required({"required": "true"})
     * @Form\Options({"label": "Password"})
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     *
     * @Form\Type("email")
     * @Form\Required({"required": "true"})
     * @Form\Options({"label": "Email"})
     */
    protected $email;

    /**
     * @ORM\Column(type="integer")
     *
     * @Form\Type("checkbox")
     * @Form\Required({"required": "true"})
     * @Form\Options({"label": "Active"})
     */
    protected $state = 1;

    /**
     * @ORM\ManyToOne(targetEntity="Role")
     */
    protected $role;

    /**
     * @ORM\ManyToMany(targetEntity="Notification")
     * @ORM\JoinTable(name="users_read_notifications")
     */
    protected $read_notifications;

    /**
     * Modification time
     *
     * @ORM\Column(type="datetime")
     */
    protected $last_login;

    public function __construct()
    {
        $this->read_notifications = new DoctrineCollection;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($name)
    {
        $this->username = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($pw)
    {
        $this->password = $pw;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($s)
    {
        $this->state = $s;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getRoleTree()
    {
        return $this->getRole() ? $this->getRole()->getTree() : [];
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getDisplayName()
    {
        return $this->getUserName();
//         throw new \Exception("Not implemented");
    }

    public function setDisplayName($name)
    {
//         throw new \Exception("Not implemented");
    }

    public function getReadNotifications()
    {
        return $this->read_notifications;
    }

    public function setReadNotifications($notifications)
    {
        $this->read_notifications = $notifications;
    }

    public function addReadNotification($notification)
    {
        $this->read_notifications[] = $notification;
    }

    public function isAdministrator()
    {
        return array_search(Role::ADMIN, $this->getRoleNames(), true) !== false;
    }

    public function getLastLogin()
    {
        return $this->last_login;
    }

    public function getRoles()
    {
        return $this->getRoleTree();
    }

    public function hasRole($role_id)
    {
        return in_array($role_id, $this->getRoleTree());
    }

    public function getRoleNames()
    {
        return array_map(function($role) { return $role->getRoleId(); }, $this->getRoles());
    }

    public function isAdmin()
    {
        foreach ($this->getRoles() as $role) {
            if ($role->getRoleId() == Role::ADMIN) {
                return true;
            }
        }
        return false;
    }

    public function setLastLogin($time)
    {
        if (!($time instanceof DateTime)) {
            $time = new DateTime($time);
        }
        $this->last_login = $time;
    }
}
