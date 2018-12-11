<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="provincial_libraries")
 */
class ProvincialLibrary extends TranslatableEntity implements SluggableInterface
{
    use SluggableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Idx\Enabled
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
     * @Idx\Translated
     */
    protected $province;

    /**
     * @ORM\Column(type="string")
     */
    protected $legacy_id;

    public function __toString()
    {
        return $this->name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getProvince()
    {
        return $this->province;
    }

    public function setProvince($name)
    {
        $this->province = $name;
    }

    public function setLegacyId($id)
    {
        $this->legacy_id = $id;
    }

    public function getLegacyId()
    {
        return $this->legacy_id;
    }
}
