<?php

namespace Kirkanta\Helmet\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Kirkanta\Entity\ServiceType;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ServiceTypeController extends AbstractActionController
{
    public static function create(ContainerInterface $container)
    {
        return new static($container->get('Doctrine\ORM\EntityManager'));
    }

    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    public function listAction()
    {
        $form = $this->entityInfo(ServiceType::class)->form('search');
        $form->setData($this->params()->fromQuery());
        $form->isValid();

        $builder = $this->entityInfo(ServiceType::class)->listBuilder();
        $builder->setFilter($form->getData());
        $result = $builder->load();

        $builder->getUrlBuilder()->setUrlPrototype('edit', 'helmet/servicetype/edit');

        $view_model =  $this->viewModel()->template('kirkanta/entity/list')->data([
            'actions' => [],
            'list' => $builder->build($result),
            'list_pager' => $result,
            'search_form' => $form,
            'title' => $builder->getTitle(),
        ]);

        $view_model->child('block_top')->template('kirkanta/entity/search-form')->data(['form' => $form]);

        return $view_model->model();
    }

    public function editAction()
    {
        $entity = $this->entity_manager->find(ServiceType::class, $this->params('id'));
        $form = $this->entityInfo($entity)->form('helmet');

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($this->formEvents()->validate($form, 'edit')) {
                $this->entity_manager->flush();
                $this->flashMessenger()->addSuccessMessage($this->tr('Data saved'));
                $this->formMessages()->addSuccessMessage($this->tr('Data saved'));

                return $this->redirect()->toRoute('helmet/servicetype/edit', $this->params()->fromRoute() + ['id' => $entity->getId()]);
            }
        }

        return $this->viewModel()->template('kirkanta/entity/edit')->data([
            'entity' => $entity,
            'form' => $form,
            'title' => $form->getTitle(),
        ])->model();
    }
}
