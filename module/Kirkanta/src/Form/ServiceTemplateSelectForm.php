<?php

namespace Kirkanta\Form;

use Kirkanta\Entity\ServiceInterface;
use Kirkanta\Util\ServiceTypes;
use Zend\Form\Fieldset;

class ServiceTemplateSelectForm extends TemplateSelectForm
{
    protected $service_types;

    protected function serviceTypeLabel(ServiceInterface $service) {
        if (!$this->service_types) {
            $this->service_types = new ServiceTypes($this->getTranslator());
        }
        return $this->service_types->map($service->getType());
    }
    
    public function setTemplateEntities(array $templates)
    {
        usort($templates, function($a, $b) {
            if ($delta = strcasecmp($this->serviceTypeLabel($a), $this->serviceTypeLabel($b))) {
                return $delta;
            }
            return strcasecmp($a->getLabel(), $b->getLabel());
        });

        foreach ($templates as $item) {
            if (!$this->has($item->getType())) {
                $this->add([
                    'type' => 'fieldset',
                    'name' => $item->getType(),
                    'options' => [
                        'label' => $this->serviceTypeLabel($item),
                    ]
                ]);
            }
            $this->addTemplateEntities($this->get($item->getType()), [$item]);
        }
    }

    public function getSelectedTemplates()
    {
        return call_user_func_array('array_merge', array_map([$this, 'getSelectedValues'], $this->getFieldsets()));
    }
}
