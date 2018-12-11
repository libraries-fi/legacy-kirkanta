<?php

namespace ServiceTool\Controller;

use ArrayObject;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Kirkanta\Entity\ServiceType;
use Kirkanta\Form\TemplateSelectForm;
use ServiceTool\ServiceTypeManager;
use ServiceTool\Form\MergeServicesForm;
use Zend\Form\FormElementManager;
use Zend\Mvc\Controller\AbstractActionController;

class AdminController extends AbstractActionController
{
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('Doctrine\ORM\EntityManager'),
            $container->get('FormElementManager'),
            $container->get(ServiceTypeManager::class)
        );
    }

    public function __construct(EntityManagerInterface $entities, FormElementManager $forms, ServiceTypeManager $service_types)
    {
        $this->em = $entities;
        $this->forms = $forms;
        $this->service_types = $service_types;
    }

    public function indexAction()
    {
        return 'moi';
    }

    public function selectServicesAction()
    {
        $items = $this->em->createQueryBuilder()
            ->select('t')
            ->from(ServiceType::class, 't')
            ->addOrderBy('t.type')
            ->addOrderBy('t.name')
            ->getQuery()->getResult();

        $form = $this->entityInfo(ServiceType::class)->form('templates', [
            'templates' => $items,
            'url_builder' => $this->entityInfo()->urlBuilder(),
        ]);

        $form->setAttribute('method', 'get');
        $form->setAttribute('action', $this->url()->fromRoute('servicetool/merge-services'));

        return [
            'form' => $form,
        ];
    }

    public function mergeServicesAction()
    {
        $params = array_filter(array_values($this->params()->fromQuery()), 'is_array');
        $selected = array_values(call_user_func_array('array_merge', $params));

        $services = $this->em->createQueryBuilder()
            ->select('t')
            ->from(ServiceType::class, 't')
            ->addOrderBy('t.tr_score', 'desc')
            ->addOrderBy('t.id')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $selected)
            ->getQuery()->getResult();

        $form = $this->forms->get(MergeServicesForm::class);

        $object = new ArrayObject([
            'service' => array_shift($services),
            'extra' => $services,
        ]);

        $form->bind($object);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->service_types->mergeInto($form->getTargetService(), $form->getExtraServices(), $form->getNameOverrides());
                $this->flashMessenger()->addSuccessMessage($this->tr('Services merged.'));
                return $this->redirect()->toRoute('servicetool/select-services');
            } else {
                var_dump('error occurred');
            }
        }

        return [
            'form' => $form,
        ];
    }
}
