<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="pictures")
 */
class Picture extends TranslatableEntity implements CreatedAwareInterface
{
    use CreatedAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $filename;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $author;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $year;

    /**
     * @ORM\Column(type="boolean", name="is_default")
     * @Idx\Enabled
     */
    protected $default;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    protected $description;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="pictures")
     */
     protected $organisation;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($file)
    {
        $this->filename = $file;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        if ($organisation) {
            $organisation->addPictures([$this]);
        }
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function isDefault()
    {
        return $this->default;
    }

    public function getDefault()
    {
        return $this->isDefault();
    }

    public function setDefault($bool)
    {
        $this->default = (bool)$bool;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }
}
