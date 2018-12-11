<?php

namespace Kirkanta\Controller\Plugin;

use Zend\Form\FormInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Kirkanta\Event\FormEvent;
use Kirkanta\Form\EntityForm;

class FormEvents extends AbstractPlugin
{
    public function __invoke($name = null, $params = [])
    {
        if (!func_num_args()) {
            return $this;
        }

        if (is_object($params) and $params instanceof FormInterface) {
            $params = [
                'form' => $params,
            ];
        }

        return $this->trigger($name, $params);
    }

    public function trigger($name, array $params)
    {
        $request = $this->getController()->getRequest();
        $match = $this->getController()->getEvent()->getRouteMatch();
        $event = new FormEvent($params['form'], $request, $match);
        $params['form']->getEventManager()->trigger($name, $event);

        /**
         * @deprecated
         */
        $this->getController()->getEventManager()->trigger($name, $event);
    }

    public function init(FormInterface $form, $form_id = null)
    {
        if ($form instanceof EntityForm) {
            $this->trigger(FormEvent::FORM_INIT, ['form' => $form]);

            if ($form_id) {
                $entity_type = $this->getController()->entityInfo($form->getObject())->aliasForClass();
                $event_id = sprintf('kirkanta.form.%s.%s.init', $entity_type, $form_id);
                $this->trigger($event_id, ['form' => $form]);
            }
        }
    }

    /**
     * Convenience function for validating form.
     *
     * Will trigger related events.
     */
    public function validate(FormInterface $form, $form_id = null)
    {
        if ($form instanceof EntityForm) {
            $this->trigger(FormEvent::FORM_VALIDATE, ['form' => $form]);
            $entity_type = $this->getController()->entityInfo($form->getObject())->aliasForClass();

            if ($form_id) {
                $event_id = sprintf('kirkanta.form.%s.%s.validate', $entity_type, $form_id);
                $this->trigger($event_id, ['form' => $form]);

                // $this->trigger(sprintf('kirkanta.form.%s.%s.validate', $entity_type, $form_id), ['form' => $form]);
            }

            $valid = $form->isValid();
            if ($valid) {
                $this->trigger(FormEvent::FORM_PROCESS, ['form' => $form]);

                if ($form_id) {
                    $event_id = sprintf('kirkanta.form.%s.%s.process', $entity_type, $form_id);
                    $this->trigger($event_id, ['form' => $form]);
                }
            }
            return $valid;
        }
        return $form->isValid();
    }
}
