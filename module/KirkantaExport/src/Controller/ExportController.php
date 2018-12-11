<?php

namespace Kirkanta\Export\Controller;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Zend\Form\FormElementManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager;

use Kirkanta\Export\AddressExporter;
use Kirkanta\Export\AddressEncoderFactory;
use Kirkanta\Export\Form\AddressesForm;

class ExportController extends AbstractActionController
{
    use ProvidesObjectManager;

    public static function create(ControllerManager $cm)
    {
        $sm = $cm->getServiceLocator();
        return new static(
            $sm->get('Doctrine\ORM\EntityManager'),
            $sm->get('FormElementManager')
        );
    }

    public function __construct(EntityManagerInterface $entity_manager, FormElementManager $form_manager)
    {
        $this->setObjectManager($entity_manager);
        $this->form_manager = $form_manager;
    }

    public function indexAction()
    {

    }

    public function addressesAction()
    {
        $form = $this->form_manager->get(AddressesForm::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($this->formEvents()->validate($form, 'export')) {
                $options = $form->getData();
                $data = (new AddressExporter($this->getObjectManager()))->export($form->getData());
                $encoded = AddressEncoderFactory::create('csv', $options)->encode($data);
                $filename = sprintf('kirjastojen osoitteet %s.%s', date('Y-m-d'), $encoded->type);

                header(sprintf('Content-Type: %s', $encoded->mime));
                header(sprintf('Content-Disposition: attachment; filename=%s', $filename));

                print($encoded->data);

            } else {
                var_dump($form->getMessages());
            }

            exit;
        }

        return [
            'form' => $form,
        ];
    }
}
