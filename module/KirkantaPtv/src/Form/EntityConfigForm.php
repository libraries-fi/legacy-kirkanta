<?php

namespace Kirkanta\Ptv\Form;

use Kirkanta\Form\Form;
use Kirkanta\Ptv\Entity\Meta;
use Zend\InputFilter\InputFilterProviderInterface;

class EntityConfigForm extends Form implements InputFilterProviderInterface
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'enabled',
            'type' => 'checkbox',
            'options' => [
                'label' => $this->tr('Enable synchronization'),
                'info' => $this->tr('Check to allow synchronization with PTV.')
            ]
        ]);

        $this->add([
            'name' => 'published',
            'type' => 'select',
            'attributes' => [
                'disabled' => true,
            ],
            'options' => [
                'label' => $this->tr('Document state'),
                'info' => $this->tr('Document state in PTV.') . ' ' . $this->tr('For now, it is not possible to synchronize documents as drafts.'),
                'value_options' => [
                    Meta::STATE_DRAFT => $this->tr('Draft'),
                    Meta::STATE_PUBLISHED => $this->tr('Published')
                ]
            ]
        ]);

        $this->add([
            'name' => 'method',
            'type' => 'radio',
            'options' => [
                'label' => $this->tr('Synchronize automatically'),
                'info' => $this->tr('Should Kirkanta synchronize this document with PTV periodically. If disabled, you will have to trigger synchronization manually.'),
                'value_options' => [
                    Meta::METHOD_MANUAL => $this->tr('No'),
                    Meta::METHOD_AUTOMATIC => $this->tr('Yes')
                ]
            ]
        ]);
    }

    public function isTranslatable()
    {
        return false;
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'enabled',
                'filters' => [
                    ['name' => 'ToInt']
                ]
            ],
            [
                'name' => 'published',
                'filters' => [
                    ['name' => 'ToInt']
                ]
            ],
            [
                'name' => 'method',
                'filters' => [
                    ['name' => 'ToInt']
                ]
            ]
        ];
    }
}
