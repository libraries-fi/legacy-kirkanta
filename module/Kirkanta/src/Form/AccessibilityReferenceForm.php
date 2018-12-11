<?php

namespace Kirkanta\Form;

class AccessibilityReferenceForm extends AccessibilityFeatureForm
{
    public function init()
    {
        parent::init();
        $this->get('name')->setAttribute('disabled', true);
        $this->get('description')->setAttribute('disabled', true);
    }

    public function getInputFilterSpecification()
    {
        return [];
    }
}
