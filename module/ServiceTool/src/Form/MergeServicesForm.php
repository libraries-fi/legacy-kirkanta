<?php

namespace ServiceTool\Form;

use Kirkanta\Form\Form;
use Kirkanta\Form\Fieldset\ServiceBaseInfo;
use Kirkanta\Hydrator\ArrayObjectHydrator;
use Kirkanta\I18n\Form\TranslatableFormInterface;
use Kirkanta\I18n\Form\TranslatableFormTrait;
use Zend\Form\Fieldset;
use Zend\Form\FieldsetInterface;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class MergeServicesForm extends Form implements TranslatableFormInterface
{
    use TranslatableFormTrait;

    protected $form_id = 'service-tool';

    public function init()
    {
        parent::init();

        $this->setHydrator(new ArrayObjectHydrator);

        $this->add([
            'name' => 'service',
            'type' => ServiceBaseInfo::class,
            'options' => [
                'label' => $this->getTranslator()->translate('Preserved instance'),
            ]
        ]);

        $this->add([
            'name' => 'extra',
            'type' => 'collection',
            'options' => [
                'label' => $this->getTranslator()->translate('Other services'),
                'count' => 0,
                'target_element' => [
                    'type' => ServiceBaseInfo::class
                ]
            ]
        ]);

        foreach ($this->get('service') as $field) {
            $field->setAttribute('disabled', false);
        }

        $this->get('service')->setHydrator(new ClassMethodsHydrator);
        $this->get('extra')->setHydrator(new ClassMethodsHydrator);

        $this->injectTranslationsElement();
        $this->injectLanguageSelector();

        array_map([$this, 'add'], $this->getStandardActions(self::SUBMIT_BUTTON));
    }

    public function bind($object, $values = self::VALUES_NORMALIZED)
    {
        parent::bind($object);
        $hydrator = $this->get('service')->getHydrator();

        $this->get('service')->setObject($object['service']);
        $this->get('service')->populateValues($hydrator->extract($object['service']));

        $this->addMergeOptions();
    }

    public function getTargetService()
    {
        return $this->get('service')->getObject();
    }

    public function getExtraServices()
    {
        return $this->get('extra')->getObject();
    }

    public function getNameOverrides()
    {
        $overrides = [];

        foreach ($this->get('extra') as $fieldset) {
            $i = $fieldset->getObject()->getId();
            if ($fieldset->get('preserve_name')->isChecked()) {
                $overrides[$i]['name'] = $fieldset->get('name')->getValue();

                foreach ($fieldset->get('translations')->getValue() as $lang => $data) {
                    $overrides[$i]['translations'][$lang] = $data['name'] ?: null;
                }
            }
        }

        // var_dump($overrides);

        return $overrides;
    }

    private function addMergeOptions()
    {
        foreach ($this->get('extra') as $fieldset) {
            $fieldset->add([
                'name' => 'preserve_name',
                'type' => 'checkbox',
                'options' => [
                    'label' => $this->tr('Copy name into service')
                ],
            ], [
                'priority' => 1000
            ]);
        }
    }
}
