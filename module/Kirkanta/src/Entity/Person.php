<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="persons")
 * @Idx\Document(type="person")
 */
class Person extends TranslatableEntity implements GroupOwnershipAwareInterface, ModifiedAwareInterface, StateAwareInterface
{
    use ElasticMappableTrait;
    use GroupOwnershipAwareTrait;
    use ModifiedAwareTrait;
    use StateAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Idx\Enabled
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @Idx\Enabled
     */
    protected $first_name;

    /**
     * @ORM\Column(type="string", length=200)
     * @Idx\Enabled
     */
    protected $last_name;

    /**
     * @ORM\Column(type="string", length=20)
     * @Idx\Enabled
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", length=255)
     * @Idx\Enabled
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean")
     * @Idx\Enabled
     */
    protected $email_public = true;

    /**
     * @ORM\Column(type="string", length=60)
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $job_title;

    /**
     * @ORM\Column(type="string", length=200)
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $responsibility;

    /**
     * @ORM\Column(type="json_array")
     * @Idx\Enabled
     */
    protected $qualities;

    /**
     * @ORM\Column(type="string", length=200)
     * @Idx\Enabled
     */
    protected $url;

    /**
     * @ORM\Column(type="boolean")
     * @Idx\Enabled
     */
    protected $is_head = false;

    /**
     * @ORM\Column(type="string", length=255)
     * @Idx\Enabled
     */
    protected $picture;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="persons")
     * @Idx\Enabled
     * @Idx\Reference(extract="field", field="id")
     */
    protected $organisation;

    public function setPicture($name)
    {
        $this->picture = $name;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function getEmailPublic()
    {
        return $this->email_public;
    }

    public function setEmailPublic($s) {
        $this->email_public = (bool) $s;
    }

    public function isEmailPublic() {
        return $this->email_public;
    }

    public function getName() {
        return sprintf('%s %s', $this->getFirstName(), $this->getLastName());
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setFirstName($name)
    {
        $this->first_name = $name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setLastName($name)
    {
        $this->last_name = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getJobTitle()
    {
        return $this->job_title;
    }

    public function setJobTitle($title)
    {
        $this->job_title = $title;
    }

    public function getResponsibility()
    {
        return $this->responsibility;
    }

    public function setResponsibility($responsibility)
    {
        $this->responsibility = $responsibility;
    }

    public function isHead()
    {
        return $this->is_head;
    }

    public function getIsHead()
    {
        return $this->isHead();
    }

    public function setIsHead($state)
    {
        $this->is_head = $state;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation($organisation)
    {
        if ($this->organisation != $organisation) {
            if ($this->organisation) {
                $this->organisation->removePersons([$this]);
            }
            if ($organisation) {
                $organisation->addPersons([$this]);
            }
            $this->organisation = $organisation;
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setQualities(array $qualities = null)
    {
        $this->qualities = $qualities;
    }

    public function getQualities()
    {
        return $this->qualities;
    }
}
