<?php

namespace Kirkanta\Finna\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kirkanta\Entity\Consortium;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\TranslatableEntityInterface;
use Kirkanta\Entity\TranslatableEntityTrait;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="finna_consortium_data")
 */
class ConsortiumData implements TranslatableEntityInterface
{
    use TranslatableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Kirkanta\Entity\Consortium", inversedBy="finna_data")
     */
    private $consortium;

    /**
     * @ORM\OneToOne(targetEntity="Kirkanta\Entity\Organisation")
     * @Idx\Enabled
     * @Idx\Reference(extract="field", field="id")
     */
    private $service_point;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    private $finna_id;

    /**
     * @ORM\Column(type="integer")
     * @Idx\Enabled
     */
    private $finna_coverage;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    private $usage_info;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     */
    private $notification;

    /**
     * @ORM\Column(type="json_array")
     */
    private $custom_data;

    public function getCustomData()
    {
        return $this->custom_data ?: [];
    }

    public function getConsortium()
    {
        return $this->consortium;
    }

    public function setConsortium(Consortium $consortium)
    {
        $this->consortium = $consortium;
    }

    public function getFinnaId()
    {
        return $this->finna_id;
    }

    public function setFinnaId($id)
    {
        $this->finna_id = $id;
    }

    public function getUsageInfo()
    {
        return $this->usage_info;
    }

    public function setUsageInfo($text)
    {
        $this->usage_info = $text;
    }

    public function getNotification()
    {
        return $this->notification;
    }

    public function setNotification($text)
    {
        $this->notification = $text;
    }

    public function setFinnaCoverage($percentage)
    {
        $this->finna_coverage = $percentage;
    }

    public function getFinnaCoverage()
    {
        return $this->finna_coverage;
    }

    public function setServicePoint(Organisation $organisation = null)
    {
        $this->service_point = $organisation;
    }

    public function getServicePoint()
    {
        return $this->service_point;
    }
}
