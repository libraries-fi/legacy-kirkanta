<?php

namespace Kirkanta\Controller;

use DateTime;
use Exception;
use Kirkanta\Entity\GroupOwnershipAwareInterface;
use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\Period;
use Kirkanta\Entity\Person;
use Kirkanta\Entity\Service;
use Kirkanta\Entity\TemplateReference;
use Kirkanta\InvalidEntityPluginException;
use Kirkanta\Event\Doctrine\QueryEventArgs;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Router\Exception\InvalidArgumentException;
use Zend\Mvc\Router\Exception\RuntimeException as RoutingException;
use Zend\View\Model\ViewModel;
use Zend\View\Model\ModelInterface as ViewModelInterface;

use Kirkanta\MobileStopListBuilder;

class OrganisationController extends EntityController
{
    public function addAction()
    {
        $organisation = new Organisation;
        $form = $this->entityInfo($organisation)->form('add');

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());

            if ($this->formEvents()->validate($form)) {
                $organisation->setGroup($this->identity()->getRole());
                $em = $this->getObjectManager();
                $em->persist($organisation);
                $em->flush();

                return $this->redirect()->toRoute('organisation/edit', $this->params()->fromRoute() + [
                    'organisation_id' => $organisation->getId(),
                ]);
            }
        }

        return [
            'form' => $form,
        ];
    }

    public function tableSortAction()
    {
        $entity_class = $this->params('resource');
        $organisation_id = $this->params('organisation_id');

        $ids = $this->params()->fromPost('rows');
        $ids = array_map('intval', $ids);

        $entities = $this->getObjectManager()->getRepository($entity_class)->findBy([
            // 'id' => $ids,
            'organisation' => $organisation_id
        ],
        [
            'weight' => 'asc',
            'id' => 'asc'
        ]);

        if ($entities) {
            foreach ($entities as $i => $entity) {
                printf("%d: %d\n", $entity->getId(), $entity->getWeight());
                $entity->setWeight($i);
            }

            printf("---------\n\n");

            $chosen = array_filter($entities, function($e) use($ids) {
                return in_array($e->getId(), $ids);
            });

            usort($chosen, function($a, $b) {
                return $a->getWeight() - $b->getWeight();
            });

            $base = reset($chosen)->getWeight();

            foreach ($chosen as $entity) {
                $new_weight = $base + array_search($entity->getId(), $ids);
                $entity->setWeight($new_weight);

                // printf("%d: %d\n", $entity->getId(), $new_weight);
            }

            foreach ($entities as $x) {
                printf("%d: %d\n", $x->getId(), $x->getWeight());
            }

            $this->getObjectManager()->flush();
        }

        exit('ok');
    }

    public function listResourcesAction()
    {
        $section = $this->params('section');
        $entity_class = $this->params('resource');
        $organisation_id = $this->params('organisation_id');
        $organisation = $this->getObjectManager()->find(Organisation::class, $organisation_id);
        $list_builder = $this->entityInfo($entity_class)->listBuilder();

        $builder = $this->getObjectManager()
            ->createQueryBuilder()
            ->select('e')
            ->from($entity_class, 'e')
            ->innerJoin(Organisation::class, 'o', 'WITH', sprintf('o.id = :id AND e MEMBER OF o.%s', $section))
            ->setParameter('id', $organisation->getId());

        $list_builder->getUrlBuilder()->setUrlPrototype('edit', 'organisation/resources/edit', $this->params()->fromRoute());

        try {
            $form = $this->entityInfo($entity_class)->form('search');
            $form->setData($this->params()->fromQuery());
            $form->isValid();
            $list_builder->setFilter($form->getData());

            if ($entity_class == Person::class) {
                $form->remove('organisation');
            }
        } catch (InvalidEntityPluginException $e) {
            $form = null;
        }

        $list_builder->setQuery($builder);
        $result = $list_builder->load();
        $list = $list_builder->build($result);

        $actions = [
            [
                'label' => $this->tr('New'),
                'route' => 'organisation/resources/edit',
                'params' => $this->params()->fromRoute(),
            ]
        ];

        if (is_a($entity_class, TemplateReference::class, true)) {
            $actions[] = [
                'label' => $this->tr('Templates'),
                'route' => 'organisation/templates',
                'params' => $this->params()->fromRoute(),
            ];

            array_shift($actions);
        }

        $view_model = $this->viewModel()->template('kirkanta/entity/list')->data([
            'organisation' => $organisation,
            'entities' => $result,
            'title' => $list_builder->getTitle(),
            'list_pager' => $result,
            'actions' => $actions,
            'list' => $list,
            'list_sortable' => in_array($section, ['phone_numbers', 'links']),
        ]);

        if ($form) {
            $view_model->child('block_top')->template('kirkanta/entity/search-form')->data(['form' => $form]);
        }

        return $view_model->model();
    }

    public function editResourceAction()
    {
        $resource_id = $this->params('id');
        $entity_class = $this->params('resource');
        $organisation = $this->getObjectManager()->find(Organisation::class, $this->params('organisation_id'));

        if ($resource_id) {
            $resource = $this->getObjectManager()->find($entity_class, $resource_id);
        } else {
            $resource = new $entity_class;
            $this->initCreatedResource($resource, $organisation);
        }

        $form = $this->entityInfo($resource)->form('edit');
        $form->bind($resource);

        // Set organisation ID if form has a field for it.
        if ($form->has('organisation')) {
            $form->get('organisation')->setValue($organisation->getId());
        }

        $form->get('cancel')->setOption('route', [
            'route' => 'organisation/resources',
            'params' => $this->params()->fromRoute(),
        ]);

        if ($form->has('delete')) {
            $form->get('delete')->setOption('route', [
                'route' => 'organisation/resources/delete',
                'params' => $this->params()->fromRoute(),
            ]);
        }

        if ($this->getRequest()->isPost()) {
            $data = array_merge_recursive($this->params()->fromFiles(), $this->params()->fromPost());
            $form->setData($data);

            if ($this->formEvents()->validate($form, 'edit')) {
                if ($resource->isNew()) {
                    $this->getObjectManager()->persist($resource);
                }

                $this->getObjectManager()->flush();
                $this->formMessages()->addSuccessMessage($this->tr('Data saved'));

                // exit('endController');


                // exit('halt');
                return $this->redirect()->toRoute('organisation/resources/edit', $this->params()->fromRoute() + ['id' => $resource->getId()]);
            } else {
                $this->formMessages()->addErrorMessage($this->tr('Form validation failed'));
            }
        }

        return [
            'title' => $form->getTitle(),
            'organisation' => $organisation,
            'resource' => $resource,
            'form' => $form,
        ];
    }

    public function deleteResourceAction()
    {
        try {
            $response = $this->forward()->dispatch(EntityController::class, [
                'action' => 'delete',
                'entity' => $this->params('resource'),
                'id' => $this->params('id'),
                'organisation_id' => $this->params('organisation_id'),
            ]);

            if ($response instanceof ViewModelInterface) {
                $form = $response->form;
                $form->get('cancel')->setOption('route', [
                    'route' => 'organisation/resources/edit',
                    'params' => $this->params()->fromRoute(),
                ]);
                return $response;
            }
        } catch (RoutingException $e) {
            // EntityController fails to find a proper route for sections that
            // aren't entity types.
        } catch (InvalidArgumentException $e) {
            // Same as above.
        }

        // exit('halt');
        return $this->redirect()->toRoute('organisation/resources', $this->params()->fromRoute());
    }

    public function templatesAction()
    {
        $entity_class = $this->params('resource');
        $template_class = $this->entityInfo($entity_class)->getInfo('template_class');
        $organisation = $this->getObjectManager()->find(Organisation::class, $this->params('organisation_id'));

        $query = $this->getObjectManager()->createQueryBuilder()
            ->select('t')
            ->from($template_class, 't');

        $event = new QueryEventArgs($query, $template_class, $this->identity());
        $this->getObjectManager()->getEventManager()->dispatchEvent(QueryEventArgs::preTemplateQuery, $event);

        $templates = $event->query->getQuery()->getResult();
        $url_builder = $this->entityInfo($entity_class)->urlBuilder();
        $url_builder->setUrlPrototype('list', 'organisation/resources', $this->params()->fromRoute());
        $options = ['templates' => $templates, 'url_builder' => $url_builder];
        $form = $this->entityInfo($template_class)->form('templates', $options);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($this->formEvents()->validate($form)) {
                $templates = $form->getSelectedTemplates();

                foreach ($templates as $template) {
                    $resource = new $entity_class;
                    $resource->setTemplate($template);
                    $resource->setOrganisation($organisation);
                    $resource->setGroup($organisation->getGroup());
                    $this->getObjectManager()->persist($resource);
                    $this->getObjectManager()->persist($template);
                }

                $this->getObjectManager()->flush();
                return $this->redirect()->toRoute('organisation/resources', $this->params()->fromRoute());
            }
        }

        return [
            'title' => $this->tr('Choose templates'),
            'form' => $form,
            'templates' => $templates,
        ];
    }

    public function mobilestopsAction()
    {
        $organisation = $this->getObjectManager()->find(Organisation::class, $this->params('organisation_id'));
        $mobile_stops = $organisation->getChildren();
        $actions = [];
        $search_form = null;

        $builder = MobileStopListBuilder::createInstance($this->getServiceLocator()->get('Kirkanta\EntityPluginManager'), Organisation::class);

        $builder->setFilter([
            'type' => 'mobile_stop',
            'parent' => $organisation->getId()
        ]);
        $result = $builder->load();

        $view_model = $this->viewModel()->template('kirkanta/entity/list')->data([
            'actions' => $actions,
            'list' => $builder->build($result),
            'list_pager' => $result,
            'search_form' => $search_form,
            // 'title' => $builder->getTitle(),
        ]);

        return $view_model->model();
    }

    public function copyPeriodAction()
    {
        $organisation = $this->getObjectManager()->find(Organisation::class, $this->params('organisation_id'));
        $templates = $this->getObjectManager()->createQueryBuilder()
            ->select('p')
            ->from(Period::class, 'p')
            ->where('p.group IS NULL or p.group IN (?0)')
            ->andWhere('p.shared = true')
            ->andWhere('p.valid_until IS NULL or p.valid_until >= ?1')
            ->orderBy('p.name')
            ->getQuery()
            ->execute([$this->identity()->getRoles(), new DateTime]);
        $url_builder = $this->entityInfo(Period::class)->urlBuilder();
        $url_builder->setUrlPrototype('list', 'organisation/resources', $this->params()->fromRoute());
        $options = ['templates' => $templates, 'url_builder' => $url_builder];
        $form = $this->entityInfo(Period::class)->form('templates', $options);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($this->formEvents()->validate($form)) {
                $templates = $form->getSelectedTemplates();

                foreach ($templates as $template) {
                    $period = new Period;
                    $period->setDays($template->getDays());
                    $period->setName($template->getName());
                    $period->setValidFrom($template->getValidFrom());
                    $period->setValidUntil($template->getValidUntil());
                    $period->setTranslations($template->getTranslations());
                    $period->setGroup($this->identity()->getRole());
                    $period->setOrganisation($organisation);
                    $period->setShared(false);

                    $this->getObjectManager()->persist($period);
                }

                $this->getObjectManager()->flush();
                return $this->redirect()->toRoute('organisation/resources', $this->params()->fromRoute());
            }
        }

        $model = new ViewModel([
            'title' => $this->tr('Choose templates'),
            'form' => $form,
            'templates' => $templates,
        ]);

        $model->setTemplate('kirkanta/organisation/templates');
        return $model;
    }

    public function copyServiceAction()
    {
        $organisation = $this->getObjectManager()->find(Organisation::class, $this->params('organisation_id'));
        $templates = $this->getObjectManager()->createQueryBuilder()
            ->select('s')
            ->from(Service::class, 's')
            ->where('s.group IS NULL or s.group IN (?0)')
            ->andWhere('s.shared = true')
            ->getQuery()
            ->execute([$this->identity()->getRoles()]);
        $url_builder = $this->entityInfo(Service::class)->urlBuilder();
        $url_builder->setUrlPrototype('list', 'organisation/resources', $this->params()->fromRoute());
        $options = ['templates' => $templates, 'url_builder' => $url_builder];
        $form = $this->entityInfo(Service::class)->form('templates', $options);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($this->formEvents()->validate($form)) {
                $templates = $form->getSelectedTemplates();

                foreach ($templates as $template) {
                    $service = new Service;
                    $service->setName($template->getName());
                    $service->setDescription($template->getDescription());
                    $service->setShortDescription($template->getShortDescription());
                    $service->setPrice($template->getPrice());
                    $service->setPicture($template->getPicture());
                    $service->setForLoan($template->getForLoan());
                    $service->setPhoneNumber($template->getPhoneNumber());
                    $service->setEmail($template->getEmail());
                    $service->setWebsite($template->getWebsite());
                    $service->setHelmetPriority($template->getHelmetPriority());
                    $service->setTemplate($template->getTemplate());

                    $service->setTranslations($template->getTranslations());
                    $service->setGroup($this->identity()->getRole());
                    $service->setOrganisation($organisation);
                    $service->setShared(false);

                    $this->getObjectManager()->persist($service);
                }

                $this->getObjectManager()->flush();
                return $this->redirect()->toRoute('organisation/resources', $this->params()->fromRoute());
            }
        }

        $model = new ViewModel([
            'title' => $this->tr('Choose templates'),
            'form' => $form,
            'templates' => $templates,
        ]);

        $model->setTemplate('kirkanta/organisation/templates');
        return $model;
    }

    protected function initCreatedResource($resource, Organisation $organisation)
    {
        if ($resource instanceof GroupOwnershipAwareInterface) {
            $resource->setGroup($organisation->getGroup());
        }

        if (method_exists($resource, 'setOrganisation')) {
            $resource->setOrganisation($organisation);
        } elseif (method_exists($resource, 'addOrganisations')) {
            $resource->addOrganisations([$organisation]);
        } else {
            throw new Exception('Cannot bind resource to organisation');
        }
    }
}
