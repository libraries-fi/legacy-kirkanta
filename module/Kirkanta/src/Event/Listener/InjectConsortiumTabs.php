<?php

namespace Kirkanta\Event\Listener;

use Interop\Container\ContainerInterface;
use Kirkanta\Controller\EntityController;
use Kirkanta\Entity\Consortium;
use Kirkanta\Event\FormEvent;
use Kirkanta\Form\ConsortiumForm;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class InjectConsortiumTabs extends AbstractListenerAggregate
{
    private $translator;

    private $form_groups = [
        'main' => ['state', 'group', 'name', 'homepage', 'slug', 'description', 'logo_file', 'logo'],
        'finna' => ['special', 'finna_data'],
        'links' => ['links'],
        'link_groups' => ['link_groups'],
    ];

    public static function create(ContainerInterface $container)
    {
        return new static($container->get('MvcTranslator'));
    }

    public function __construct($translator)
    {
        $this->translator = $translator;
        $this->events = [
            [EntityController::class, MvcEvent::EVENT_DISPATCH, [$this, 'injectTabs']],
            [ConsortiumForm::class, 'kirkanta.form.consortium.edit.init', [$this, 'initializeForm']],
        ];
    }

    private function tr($text)
    {
        return $this->translator->translate($text);
    }

    public function injectTabs(MvcEvent $event)
    {
        $route_match = $event->getRouteMatch();
        $entity_class = $route_match->getParam('entity');
        $entity = $event->getRouteMatch()->getParam('object');
        $tab = $route_match->getParam('tab');

        if ($tab && $entity_class == Consortium::class) {
            $tabs = new ViewModel([
                'active' => $tab,
                'tabs' => (object)[
                    'main' => (object)[
                        'route' => null,
                        'title' => $this->tr('Base info'),
                        'disabled' => $entity->isNew(),
                        'params' => [
                            'tab' => 'main'
                        ]
                    ],
                    'finna' => (object)[
                        'route' => null,
                        'title' => $this->tr('Finna'),
                        'disabled' => $entity->isNew(),
                        'params' => [
                            'tab' => 'finna'
                        ]
                    ],
                    'links' => (object)[
                        'route' => null,
                        'title' => $this->tr('Links'),
                        'disabled' => $entity->isNew(),
                        'params' => [
                            'tab' => 'links'
                        ]
                    ],
                    'link_groups' => (object)[
                        'route' => null,
                        'title' => $this->tr('Link groups'),
                        'disabled' => $entity->isNew(),
                        'params' => [
                            'tab' => 'link_groups'
                        ]
                    ],
                ]
            ]);

            $tabs->setTemplate('kirkanta/consortium/form-tabs');
            $event->getViewModel()->addChild($tabs, 'block_layout_main_top');
        }
    }

    public function initializeForm(FormEvent $event)
    {
        $form = $event->getForm();
        $tab = $event->getRouteMatch()->getParam('tab');
        $validation = $this->form_groups[$tab];

        if ($tab != 'main') {
            $form->remove('delete');
        }

        if (!$form->has('group') && in_array('group', $validation)) {
            unset($validation[array_search('group', $validation)]);
        }

        $fields = array_merge(['language', 'translations'], $validation);
        $form->setValidationGroup($fields);
    }
}
