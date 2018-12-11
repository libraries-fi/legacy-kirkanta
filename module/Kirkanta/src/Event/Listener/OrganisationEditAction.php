<?php

namespace Kirkanta\Event\Listener;

use BjyAuthorize\Service\Authorize;
use Interop\Container\ContainerInterface;
use Kirkanta\Controller\EntityController;
use Kirkanta\Controller\OrganisationController;
use Kirkanta\Event\FormEvent;
use Kirkanta\Entity\Organisation;
use Kirkanta\Form\OrganisationForm;
use Zend\Form\FormInterface;
use Zend\Mvc\MvcEvent;

class OrganisationEditAction extends AbstractListenerAggregate
{
    protected $sections = [
        'basics' => [
            'name',
            'short_name',
            'type',
            'branch_type',
            'parent',
            'city',
            'consortium',
            'isil',
            'identificator',
            'slogan',
            'description',
        ],
        'description' => [
            'legacy_description',
        ],
        'addresses' => [
            'coordinates',
            'address',
            'mail_address',
        ],
        'phone_numbers' => [
            'phone_numbers',
        ],
        'periods' => [
            'periods',
        ],
        'pictures' => [
            'pictures',
        ],
        'services' => [
            'services',
        ],
        'accessibility' => [
            'accessibility',
        ],
        'persons' => [
            'persons',
        ],
        'custom_data' => [
            'custom_data',
        ],
        'transit' => [
            'buses',
            'trams',
            'trains',
            'transit_directions',
            'parking_instructions',

        ],
        'misc' => [
            'email',
            'homepage',
            'web_library',
            'construction_year',
            'building_name',
            'building_architect',
            'interior_designer',
        ],
    ];

    public static function create(ContainerInterface $container)
    {
        return new static($container->get(Authorize::class));
    }

    public function __construct(Authorize $auth)
    {
        $this->auth = $auth;

        $this->events = [
            [OrganisationForm::class, 'kirkanta.form.organisation.edit.validate', [$this, 'validateEditForm']],
            [EntityController::class, MvcEvent::EVENT_DISPATCH, [$this, 'alterViewParams'], -1000],
            [OrganisationController::class, MvcEvent::EVENT_DISPATCH, [$this, 'injectScheduleCopyAction'], -1000],
            [OrganisationController::class, MvcEvent::EVENT_DISPATCH, [$this, 'injectServiceCopyAction'], -1000],
        ];
    }

    public function validateEditForm(FormEvent $event)
    {
        $section = $event->getRouteMatch()->getParam('section');
        $fields = $this->formFields($section, $event->getForm());
        if ($fields) {
            if ($section == 'addresses' and !$event->getForm()->get('mail_address')->get('enabled')->isChecked()) {
                $i = array_search('mail_address', $fields);
                unset($fields[$i]);
                $event->getForm()->getObject()->setMailAddress(null);
            }
            $event->getForm()->setValidationGroup($fields);
        }
    }

    public function alterViewParams(MvcEvent $event)
    {
        if ($event->getRouteMatch()->getMatchedRouteName() == 'organisation/edit') {
            $event->getResult()->setTemplate('kirkanta/organisation/edit');
            $section = $event->getRouteMatch()->getParam('section');
            $fields = $this->formFields($section, $event->getResult()->getVariable('form'));

            if ($fields) {
                $event->getResult()->setVariable('fields', $fields);
            }
        }
    }

    public function injectScheduleCopyAction(MvcEvent $event)
    {
        $route = $event->getRouteMatch()->getMatchedRouteName();
        $section = $event->getRouteMatch()->getParam('section');
        if ($route == 'organisation/resources' and $section == 'periods') {
            $actions = $event->getResult()->getVariable('actions');
            $actions[] = [
                'label' => $event->getTarget()->tr('Copy template'),
                'route' => 'organisation/copy_period',
                'params' => $event->getRouteMatch()->getParams(),
            ];
            $event->getResult()->setVariable('actions', $actions);
        }
    }

    public function injectServiceCopyAction(MvcEvent $event)
    {
        $route = $event->getRouteMatch()->getMatchedRouteName();
        $section = $event->getRouteMatch()->getParam('section');
        if ($route == 'organisation/resources' and $section == 'services') {
            $actions = $event->getResult()->getVariable('actions');
            $actions[] = [
                'label' => $event->getTarget()->tr('Copy template'),
                'route' => 'organisation/copy_service',
                'params' => $event->getRouteMatch()->getParams(),
            ];
            $event->getResult()->setVariable('actions', $actions);
        }
    }

    private function formFields($section, FormInterface $form)
    {
        if (isset($this->sections[$section])) {
            $base_fields = $section == 'basics' ? ['state', 'translations'] : ['translations'];
            $fields = array_merge($base_fields, $this->sections[$section]);

            if ($section == 'basics' && $this->auth->isAllowed('entity', 'admin')) {
                $fields = array_merge(['group'], $fields);
            }

            if ($section == 'misc' && $this->auth->isAllowed('entity', 'admin')) {
                $fields = array_merge(['slug'], $fields);
            }

            if ($section == 'misc' && $form->has('helmet_sierra_id')) {
                $fields[] = 'helmet_sierra_id';
            }

            return $fields;
        }
    }
}
