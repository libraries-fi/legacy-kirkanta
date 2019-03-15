<?php

namespace Kirkanta\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity(repositoryClass="Kirkanta\Doctrine\Repository\OrganisationRepository")
 * @ORM\Table(name="organisations")
 * @Idx\Document(type="organisation")
 */
class Organisation extends TranslatableEntity implements GroupOwnershipAwareInterface, ModifiedAwareInterface, SluggableInterface, StateAwareInterface
{
    use ElasticMappableTrait;
    use GroupOwnershipAwareTrait;
    use ModifiedAwareTrait;
    use SluggableTrait;
    use StateAwareTrait;

    const STREET_ADDRESS = 0;
    const MAIL_ADDRESS = 1;

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
     * @Idx\Translated(fallback=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $short_name;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $branch_type;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     * @Idx\Group(into="extra")
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     * @Idx\Group(into="extra")
     */
    protected $legacy_description;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     * @Idx\Group(into="extra")
     */
    protected $slogan;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="extra")
     */
    protected $isil;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="extra")
     */
    protected $identificator;

    /**
     * @ORM\Column(type="integer")
     * @Idx\Enabled
     * @Idx\Group(into="extra")
     */
    protected $founded;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $homepage;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     */
    protected $web_library;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="transit")
     */
    protected $buses;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="transit")
     */
    protected $trains;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="transit")
     */
    protected $trams;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     * @Idx\Group(into="transit")
     */
    protected $transit_directions;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated
     * @Idx\Group(into="transit")
     */
    protected $parking_instructions;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Translated(fallback=true)
     * @Idx\Group(into="building")
     */
    protected $building_name;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="building")
     */
    protected $building_architect;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="building")
     */
    protected $interior_designer;

    /**
     * @ORM\Column(type="integer")
     * @Idx\Enabled
     * @Idx\Group(into="building")
     */
    protected $construction_year;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     *
     * NOTE: Upon indexing, this field is manually moved under address group.
     * This is because annotation Group doesn't work with address field's Reference.
     */
    protected $coordinates;

    /**
     * @ORM\Column(type="string")
     * @Idx\Enabled
     * @Idx\Group(into="extra")
     */
    protected $helmet_sierra_id;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="children")
     * @Idx\Enabled
     * @Idx\Reference(extract="field", field="id")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Organisation", mappedBy="parent")
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="Organisation", mappedBy="mobile_library")
     */
    protected $mobile_stops;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="mobile_stops")
     */
    protected $mobile_library;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="organisations")
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="Consortium", inversedBy="organisations")
     * @Idx\Enabled
     * @Idx\Reference
     *
     * NOTE: NULL value means fallbacking to the value that is set for this
     * Organisations's referenced City entity. To disable this fallback, set
     * $force_no_consortium to TRUE.
     */
    protected $consortium;

    /**
     * @ORM\Column(type="boolean")
     *
     * NOTE: This has to be set to true when we want to disallow fallback consortium.
     */
    protected $force_no_consortium = false;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"remove", "persist"}, orphanRemoval=true)
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $address;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"remove", "persist"}, orphanRemoval=true)
     * @Idx\Enabled
     * @Idx\Reference
     */
    protected $mail_address;

    /**
     * @ORM\OneToMany(targetEntity="Service", mappedBy="organisation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $services;

    /**
     * @ORM\OneToMany(targetEntity="AccessibilityReference", mappedBy="organisation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $accessibility;

    /**
     * @ORM\OneToMany(targetEntity="Period", mappedBy="organisation", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $periods;

    /**
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="organisation", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC", "id": "ASC"})
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="organisation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $pictures;

    /**
     * @ORM\OneToMany(targetEntity="OrganisationWebLink", mappedBy="organisation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC", "id": "ASC"})
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $links;

    /**
     * @ORM\OneToMany(targetEntity="OrganisationWebLinkGroup", mappedBy="organisation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $link_groups;

    /**
     * @ORM\OneToMany(targetEntity="Person", mappedBy="organisation", cascade={"persist", "remove"})
     * @Idx\Enabled
     * @Idx\Reference(type="list")
     */
    protected $persons;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $custom_data;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $cached_legacy_times;

    public function __construct()
    {
        $this->accessibility = new ArrayCollection;
        $this->children = new ArrayCollection;
        $this->links = new ArrayCollection;
        $this->mobile_stops = new ArrayCollection;
        $this->periods = new ArrayCollection;
        $this->persons = new ArrayCollection;
        $this->phone_numbers = new ArrayCollection;
        $this->pictures = new ArrayCollection;
        $this->services = new ArrayCollection;

        $this->weblinks = new ArrayCollection;
        $this->link_groups = new ArrayCollection;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Organisation $organisation = null)
    {
        $this->parent = $organisation;
    }

    public function setRemoved($state)
    {
        $this->state = $state ? -1 : 0;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($d)
    {
        $this->description = $d;
    }

    public function getLegacyDescription()
    {
        return $this->legacy_description;
    }

    public function setLegacyDescription($d)
    {
        $this->legacy_description = $d;
    }

    public function getSlogan()
    {
        return $this->slogan;
    }

    public function setSlogan($d)
    {
        $this->slogan = $d;
    }

    public function getIsil()
    {
        return $this->isil;
    }

    public function setIsil($i)
    {
        $this->isil = $i;
    }

    public function getIdentificator()
    {
        return $this->identificator;
    }

    public function setIdentificator($i)
    {
        $this->identificator = $i;
    }

    public function getFounded()
    {
        return $this->founded;
    }

    public function setFounded($f)
    {
        $this->founded = $f;
    }

    public function getHomepage()
    {
        return $this->homepage;
    }

    public function getWebLibrary($fallback = false)
    {
        if ($fallback && !$this->web_library) {
            if ($c = $this->getConsortium(true)) {
                return $c->getHomepage();
            }
        }
        return $this->web_library;
    }

    public function setWebLibrary($url)
    {
        $this->web_library = $url;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($e)
    {
        $this->email = $e;
    }

    public function getTransitDirections()
    {
        return $this->transit_directions;
    }

    public function setTransitDirections($t)
    {
        $this->transit_directions = $t;
    }

    public function getParkingInstructions()
    {
        return $this->parking_instructions;
    }

    public function setParkingInstructions($p)
    {
        $this->parking_instructions = $p;
    }

    public function getBuildingArchitect()
    {
        return $this->building_architect;
    }

    public function setBuildingArchitect($a)
    {
        $this->building_architect = $a;
    }

    public function getInteriorDesigner()
    {
        return $this->interior_designer;
    }

    public function setInteriorDesigner($d)
    {
        $this->interior_designer = $d;
    }

    public function getCoordinates()
    {
        return $this->coordinates;
    }

    public function setCoordinates($c)
    {
        $this->coordinates = $c;
    }

    public function getCity()
    {
        if ($this->getAddress()) {
            return $this->getAddress()->getCity();
        }
        return $this->city;
    }

    public function setCity(City $city = null)
    {
        if ($this->getAddress()) {
            $this->getAddress()->setCity($city);
        }
        $this->city = $city;
    }

    public function getPhoneNumbers()
    {
        return $this->phone_numbers;
    }

    public function setPhoneNumbers(array $items)
    {
        $this->phone_numbers = new ArrayCollection($items);

        foreach ($items as $item) {
            $item->setOrganisation($this);
        }
    }

    public function getServices()
    {
        return $this->services;
    }

    public function setServices(array $items)
    {
        $this->services = new ArrayCollection($items);

        foreach ($items as $item) {
            $item->setOrganisation($this);
        }
    }

    public function getPictures()
    {
        return $this->pictures;
    }

    public function setPictures(array $items)
    {
        $this->pictures = new ArrayCollection($items);

        foreach ($items as $item) {
            $item->setOrganisation($this);
        }
    }

    public function getPeriods($section = null)
    {
        if (!$section) {
            return $this->periods;
        }

        $periods = array_filter($this->periods->toArray(), function($period) use($section) {
            return $section == $period->getSection();
        });

        return new ArrayCollection($periods);
    }

    public function setPeriods(array $items)
    {
        $this->periods = new ArrayCollection($items);

        foreach ($items as $item) {
            $item->setOrganisation($this);
        }
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = (int)$state;
    }

    public function isPublished()
    {
        return $this->getState() > 0;
    }

    public function delete()
    {
         $this->state = self::STATE_DELETED;
    }

    public function isDeleted()
    {
        return $this->state ==  self::STATE_DELETED;
    }

    public function getConstructionYear()
    {
        return $this->construction_year;
    }

    public function setConstructionYear($year)
    {
        $this->construction_year = (int) $year;
    }

    public function getBuildingName()
    {
        return $this->building_name;
    }

    public function setBuildingName($name)
    {
        return $this->building_name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getBranchType()
    {
        return $this->branch_type;
    }

    public function setBranchType($type)
    {
        $this->branch_type = $type;
    }

    public function addServices($items)
    {
        $this->addItems($this->services, $items, 'setOrganisation');
    }

    public function removeServices($items)
    {
        $this->removeItems($this->services, $items, 'setOrganisation');
    }

    public function addPictures($items)
    {
        $this->addItems($this->pictures, $items);
    }

    public function removePictures($items)
    {
        $this->removeItems($this->pictures, $items, 'setOrganisation');
    }

    public function addPeriods($items)
    {
        $this->addItems($this->periods, $items, 'setOrganisation');
    }

    public function removePeriods($items)
    {
        $this->removeItems($this->periods, $items, 'setOrganisation');
    }

    public function addAccessibility($items)
    {
        $this->addItems($this->accessibility, $items, 'setOrganisation');
    }

    public function removeAccessibility($items)
    {
        $this->removeItems($this->accessibility, $items, 'setOrganisation');
    }

    public function getAccessibility()
    {
        return $this->accessibility;
    }

    public function getPersons()
    {
        return $this->persons;
    }

    public function addPersons($items)
    {
        $this->addItems($this->persons, $items);
    }

    public function removePersons($items)
    {
        $this->removeItems($this->persons, $items, 'setOrganisation');
    }

    public function addPhoneNumbers($items)
    {
        $this->addItems($this->phone_numbers, $items, 'setOrganisation');
    }

    public function removePhoneNumbers($items)
    {
        $this->removeItems($this->phone_numbers, $items, 'setOrganisation');
    }

    public function setPersons($items)
    {
        $this->addItems($this->persons, $items, 'setOrganisation');
    }

    public function addMobileStops($stops)
    {
        $this->addItems($this->mobile_stops, $items, 'setMobileLibrary');
    }

    public function getMobileStops()
    {
        return $this->mobile_stops;
    }

    public function getMobileLibrary()
    {
        return $this->mobile_library;
    }

    public function setMobileLibrary(Organisation $library = null)
    {
        return $this->mobile_library = $library;
    }

    public function getShortName()
    {
        return $this->short_name;
    }

    public function setShortName($name)
    {
        $this->short_name = $name;
    }

    public function setHomepage($url)
    {
        $this->homepage = $url;
    }

    public function setAddress(Address $a = null)
    {
        if ($a && $a->isNull()) {
            $a = null;
        }
        $this->address = $a;

        if ($this->address) {
            $this->address->setOrganisation($this);
        }

        // Ensure that value of city_id will be kept the same in both
        // organisations and addressess tables!
        // NOTE: Cross-settings city values will cause trouble,
        // if both city and address fields are on the same form view!
        $this->city = $this->getCity();
    }

    public function setAccessibility(array $items)
    {
        $this->accessibility = new ArrayCollection($items);
        foreach ($items as $item) {
            $item->setOrganisation($this);
        }
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setMailAddress(Address $a = null)
    {
        if ($a && $a->isNull()) {
            $a = null;
        }
        $this->mail_address = $a;

        if ($this->mail_address) {
            $this->mail_address->setOrganisation($this);
        }
    }

    public function getMailAddress()
    {
        return $this->mail_address;
    }

    public function setLinks(array $items)
    {
        $this->links = new ArrayCollection($items);
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function setLinkGroups(array $items)
    {
        $this->link_groups = new ArrayCollection($items);
    }

    public function getLinkGroups()
    {
        return $this->link_groups;
    }

    public function getFallbackConsortium()
    {
        if ($this->city) {
            return $this->city->getConsortium();
        }
    }

    public function isFallbackConsortiumAllowed()
    {
        return $this->force_no_consortium == false;
    }

    public function setFallbackConsortiumAllowed($state)
    {
        $this->force_no_consortium = ($state == false);

        if ($this->force_no_consortium) {
            $this->consortium = null;
        }
    }

    public function getConsortium($fallback = false)
    {
        if ($this->consortium) {
            return $this->consortium;
        }
        if ($fallback && $this->isFallbackConsortiumAllowed()) {
            return $this->getFallbackConsortium();
        }
    }

    public function setConsortium($consortium)
    {
        if ($consortium) {
            $this->force_no_consortium = false;
        }

        // We prefer to fallback to shared consortium data.
        if ($this->getFallbackConsortium() == $consortium) {
            $this->consortium = null;
        } else {
            $this->consortium = $consortium;
        }
    }

    public function getCustomData()
    {
        return $this->custom_data ?: [];
    }

    public function setCustomData($data)
    {
        $this->custom_data = $data;
    }

    public function getBuses()
    {
        return $this->buses;
    }

    public function setBuses($string)
    {
        $this->buses = $string;
    }

    public function getTrams()
    {
        return $this->trams;
    }

    public function setTrams($string)
    {
        $this->trams = $string;
    }

    public function getTrains()
    {
        return $this->trains;
    }

    public function setTrains($string)
    {
        $this->trains = $string;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addLinks($items)
    {
        $this->addItems($this->links, $items, 'setOrganisation');
    }

    public function removeLinks($items)
    {
        $this->removeItems($this->links, $items);
    }

    public function addLinkGroups($items)
    {
        $this->addItems($this->link_groups, $items, 'setOrganisation');
    }

    public function removeLinkGroups($items)
    {
        $this->removeItems($this->link_groups, $items);
    }

    public function setCachedLegacyTimes($times)
    {
        $this->cached_legacy_times = $times;
    }

    public function getCachedLegacyTimes()
    {
        return $this->cached_legacy_times;
    }

    public function getHelmetSierraId()
    {
        return $this->helmet_sierra_id;
    }

    public function setHelmetSierraId($id)
    {
        $this->helmet_sierra_id = $id;
    }
}
