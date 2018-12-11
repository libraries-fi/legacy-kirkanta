<?php

namespace Kirkanta\Entity;

use Doctrine\ORM\Mapping as ORM;
use KirkantaIndexing\Annotation as Idx;

/**
 * @ORM\Entity
 * @ORM\Table(name="template_references")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="entity_type", type="string")
 * @ORM\DiscriminatorMap({"accessibility" = "AccessibilityReference"})
 */
abstract class TemplateReference extends TranslatableEntity implements GroupOwnershipAwareInterface, ModifiedAwareInterface
{
    use GroupOwnershipAwareTrait;
    use ModifiedAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="json_array")
     * @Idx\Enabled
     * @Idx\Merge
     */
    protected $overrides = [];

    /**
     * @ORM\Column(type="json_array")
     * @Idx\Enabled
     */
    protected $translations = [];

    public function setOverrides(array $values)
    {
        $this->overrides = $values;
    }

    public function getOverrides()
    {
        return $this->overrides;
    }

    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOverride($key, $value)
    {
        $this->overrides[$key] = $value;
    }

    public function getOverride($key, $getter)
    {
        if (isset($this->overrides[$key])) {
            return $this->overrides[$key];
        } else {
            $source = $this->{$this->_source_field};
            return call_user_func([$source, $getter]);
        }
    }

    public function setTemplate($entity)
    {
        $this->{$this->_source_field} = $entity;
    }

    public function getTranslations()
    {
        $translations = $this->{$this->_source_field}->getTranslations();

        foreach ($this->translations as $lang => $data) {
            $translations[$lang] = array_merge(array_get($translations, $lang, []), $data);
        }

        return $translations;
    }

}
