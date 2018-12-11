<?php

namespace Kirkanta\Controller;

use DateTime;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Interop\Container\ContainerInterface;
use Kirkanta\Entity\GroupOwnershipAwareInterface;
use Kirkanta\Entity\ModifiedAwareInterface;
use Kirkanta\Entity\ServiceType;
use Kirkanta\Entity\SharedEntityInterface;
use Kirkanta\InvalidEntityPluginException;
use Kirkanta\Ptv\PtvManager;
use Zend\Form\FormElementManager;
use Zend\Mvc\Controller\AbstractActionController;

class EntityController extends AbstractActionController
{
    use ProvidesObjectManager;

    private $form_manager;

    public static function create(ContainerInterface $container)
    {
        $router = $container->get('Router');
        $request = $container->get('Request');
        $match = $router->match($request);

        return new static(
            $container->get('Doctrine\ORM\EntityManager'),
            $container->get('FormElementManager'),
            $container->get(PtvManager::class)
        );
    }

    public function __construct(EntityManagerInterface $entity_manager, FormElementManager $form_manager, PtvManager $ptv_manager)
    {
        $this->setObjectManager($entity_manager);
        $this->form_manager = $form_manager;
        $this->ptv = $ptv_manager;
    }

    public function listAction()
    {
        $entity_class = $this->params('entity');
        $entity_alias = $this->entityInfo($entity_class)->aliasForClass();
        $repository = $this->entityInfo($entity_class)->repository();
        $config = $this->entityInfo($entity_class)->config();
        $builder = $this->entityInfo($entity_class)->listBuilder();
        $filter = [];

        if (!$this->isAllowed('entity.' . $entity_alias, 'admin') && is_a($entity_class, GroupOwnershipAwareInterface::class, true)) {
            $filter['group'] = $this->identity()->getRoleTree();
        }

        if (is_a($entity_class, SharedEntityInterface::class, true)) {
            $filter['shared'] = true;
        }

        try {
            $form = $this->entityInfo($entity_class)->form('search');
            $form->setData($this->params()->fromQuery());
            $form->isValid();
            $filter += $form->getData();
        } catch (InvalidEntityPluginException $e) {
            // pass
            $form = null;
        }

        $builder->setFilter($filter);
        $result = $builder->load();

        $actions = [
            [
                'label' => $this->tr('New'),
                'route' => $this->entityInfo()->aliasForClass($entity_class) . '/add',
                'params' => $this->params()->fromRoute(),
            ]
        ];

        $view_model = $this->viewModel()->template('kirkanta/entity/list')->data([
            'actions' => $actions,
            'list' => $builder->build($result),
            'list_pager' => $result,
            'search_form' => $form,
            'title' => $builder->getTitle(),
        ]);

        if ($form) {
            $view_model->child('block_top')->template('kirkanta/entity/search-form')->data(['form' => $form]);
        };

        return $view_model->model();
    }

    public function editAction()
    {
        $entity_class = $this->params('entity');
        $id_param = $this->entityInfo($entity_class)->idParam();
        $entity_id = $this->params($id_param, $this->params('id'));
        $entity = $this->entityInfo($entity_class)->repository()->findOneById($entity_id) ?: new $entity_class;
        $form = $this->entityInfo($entity)->form('edit');

        if ($this->getRequest()->isPost()) {
            $data = array_merge($this->params()->fromPost(), $this->params()->fromFiles());
            $form->setData($data);

            if ($this->formEvents()->validate($form, 'edit')) {
                if ($entity->isNew()) {
                    $this->getObjectManager()->persist($entity);

                    if ($entity instanceof GroupOwnershipAwareInterface) {
                        $entity->setGroup($this->identity()->getRole());
                    }

                    if ($entity instanceof SharedEntityInterface) {
                        $entity->setShared(true);

                        if ($this->identity()->isAdministrator()) {
                            /*
                             * Admins share templates globally!
                             * NOTE: Template entities implement GroupOwnershipAwareInterface
                             */
                            $entity->setGroup(null);
                        }
                    }
                }

                /*
                 * This should be made into an event handler, but so far haven't
                 * figured out how to make it work without breaking cascading
                 * entity relations.
                 */
                if ($entity instanceof ModifiedAwareInterface) {
                    $entity->setModified(new DateTime);
                }

                try {
                    $this->getObjectManager()->flush();
                    $this->flashMessenger()->addSuccessMessage($this->tr('Data saved'));
                    $this->formMessages()->addSuccessMessage($this->tr('Data saved'));
                } catch (ForeignKeyConstraintViolationException $exception) {
                    $this->formMessages()->addErrorMessage($this->tr('Changes could not be saved because the removed record has child resources.') . ' (Foreign key violation)');
                }

                /*
                 * FIXME: Tried to do this via an event listener but they aren't triggered if
                 * controller returns a 'redirect'...
                 */
                $meta = $this->ptv->getEntityMeta($entity);

                if ($meta && $meta->getMethod() == 1) {
                    $route = 'kirkanta_ptv/synchronize';
                    $params = [
                        'type' => $this->entityInfo()->aliasForClass($entity_class),
                        'id' => $entity_id,
                    ];
                } else {
                    $route = $this->entityInfo($entity)->route('edit');
                    $params = $this->params()->fromRoute() + ['id' => $entity->getId()];
                }
                return $this->redirect()->toRoute($route, $params);
            } else {
                $this->formMessages()->addErrorMessage($this->tr('Validation failed'));
            }
        }

        if (!($title = $form->getTitle())) {
            if ($entity->isNew()) {
                $title = $this->tr('New resource');
            } else {
                $title = $this->tr('Edit');

                if (method_exists($entity, '__toString')) {
                    $title .= ' ' . $entity;
                }
            }
        }

        return $this->viewModel()->template('kirkanta/entity/edit')->data([
            'entity' => $entity,
            'form' => $form,
            'title' => $title,
        ])->model();
    }

    public function deleteAction()
    {
        $entity_class = $this->params('entity');
        $id_param = $this->entityInfo($entity_class)->idParam();
        $entity_id = $this->params('id', $this->params($id_param));

        $entity = $this->entityInfo($entity_class)->repository()->findOneById($entity_id);
        $form = $this->entityInfo($entity)->form('delete');

        if ($this->getRequest()->isPost()) {
            if ($form->isValid()) {
                $this->getObjectManager()->remove($entity);
                $this->getObjectManager()->flush();
                $this->flashMessenger()->addSuccessMessage($this->tr('Item removed'));
                return $this->redirect()->toRoute($this->entityInfo($entity_class)->route('list'));
            } else {
                $this->formMessages()->addErrorMessage($this->tr('Validation failed'));
            }
        }

        return $this->viewModel()->template('kirkanta/entity/delete')->data([
            'entity' => $entity,
            'form' => $form,
            'title' => $form->getTitle(),
        ])->model();
    }
}
