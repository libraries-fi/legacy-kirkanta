<?php

namespace Kirkanta\Ptv\Controller;

use DateTime;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Kirkanta\Ptv\Entity\Meta;
use Kirkanta\Ptv\Form\EntityConfigForm;
use Kirkanta\Ptv\PtvManager;
use Kirkanta\Ptv\ServiceLocationMapper;
use Kirkanta\Ptv\SynchronizationException;
use Kirkanta\Ptv\ValidationException;
use Zend\Authentication\AuthenticationService;
use Zend\Form\FormElementManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class PtvController extends AbstractActionController
{
    private $entity_manager;
    private $form_manager;
    private $auth;
    private $ptv;

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('Doctrine\ORM\EntityManager'),
            $container->get('FormElementManager'),
            $container->get('Zend\Authentication\AuthenticationService'),
            $container->get(PtvManager::class)
        );
    }

    public function __construct(EntityManagerInterface $entity_manager, FormElementManager $form_manager, AuthenticationService $auth, PtvManager $ptv_manager)
    {
        $this->entity_manager = $entity_manager;
        $this->form_manager = $form_manager;
        $this->auth = $auth;
        $this->ptv = $ptv_manager;
    }

    public function configureAction()
    {
        $type = $this->params('type');
        $class = $this->entityInfo()->classForAlias($type);
        $id = $this->params('id');

        $entity = $this->entity_manager->getRepository($class)->findOneById($id);

        $meta = $this->ptv->getEntityMeta($entity);

        if (!$meta) {
            $meta = new Meta;
            $meta->setEntityId($id);
            $meta->setEntityType($type);
        }

        $form = $this->form_manager->get(EntityConfigForm::class);
        $form->bind($meta);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->entity_manager->persist($meta);
                $this->entity_manager->flush();

                $this->formMessages()->addSuccessMessage($this->tr('Configuration saved'));

                return $this->redirect()->refresh();
            } else {
                $this->formMessages()->addErrorMessage('Validation failed');
            }
        }

        return [
            'form' => $form,
            'meta' => $meta,
            'entity' => $entity,
        ];
    }

    public function validateAction()
    {
        $type = $this->params('type');
        $class = $this->entityInfo()->classForAlias($type);
        $id = $this->params('id');

        $entity = $this->entity_manager->getRepository($class)->findOneById($id);
        $meta = $this->ptv->getEntityMeta($entity, true);


        if ($meta) {
            try {
                $this->ptv->getMapper($entity)->convert($entity, $meta);
                $data = ['valid' => true];
            } catch (ValidationException $e) {
                $data = [
                    'valid' => false,
                    'errors' => $e->getErrors()
                ];
            }
        }

        return $this->viewModel()->data($data)->terminal()->model();
    }

    public function synchronizeAction()
    {
        $data = $this->params()->fromRoute();
        $session = new Container('ptv_sync');

        if ($session->result) {
            $data += $session->result->getVariables()->getArrayCopy();
            unset($session->result);
        }

        if ($this->getRequest()->isPost()) {
            $result = $this->forward()->dispatch(PtvController::class, [
                'action' => 'runSynchronization'
            ] + $this->params()->fromRoute());

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $result;
            } else {
                $session->result = $result->getVariables()->getArrayCopy();
                return $this->redirect()->refresh();
            }
        }

        return $this->viewModel()->data($data)->model();
    }

    public function runSynchronizationAction()
    {
        $entity_type = $this->params('type');
        $entity_class = $this->entityInfo()->classForAlias($entity_type);
        $entity_id = (int)$this->params('id');

        $entity = $this->entityInfo($entity_class)->repository()->findOneById($entity_id);

        try {
            $result = $this->ptv->sync($entity);

            $this->viewModel()->data([
                'status' => 'ok',
                'data' => $result,
            ]);

        } catch (ValidationException $e) {
            $this->viewModel()->data([
                'status' => 'validation_error',
                'errors' => $e->getErrors(),
            ]);
        } catch (SynchronizationException $e) {
            $errors = json_decode((string)$e->getPrevious()->getResponse()->getBody());

            $errors = get_object_vars($errors);

            $this->viewModel()->data([
                'status' => 'synchronization_error',
                'errors' => $errors,
                'document' => $e->getPtvDocument(),
            ]);
        }

        $this->entity_manager->flush();
        return $this->viewModel()->template('kirkanta/ptv/results')->model();
    }

    protected function getEntityManager()
    {
        return $this->entity_manager;
    }
}
