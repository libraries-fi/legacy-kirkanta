<?php

namespace Kirkanta\Controller;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Zend\Form\FormElementManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

use Kirkanta\Entity\Notification;

class AccountController extends AbstractActionController
{
    use ProvidesObjectManager;

    public static function create(ServiceLocatorInterface $sm)
    {
        return new static(
            $sm->get('Doctrine\ORM\EntityManager')
        );
    }

    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->setObjectManager($entity_manager);
    }

    public function notificationsAction()
    {
        $unread = $this->entityInfo(Notification::class)->repository()->findUnreadByUser($this->identity());
        $ids = array_flip(array_map(function($n) { return $n->getId(); }, $unread));

        $builder = $this->entityInfo(Notification::class)->listBuilder();

        $builder->getUrlBuilder()->setUrlPrototype('edit', 'account/notifications/read');

        $result = $builder->load();
        $table = $builder->build($result);

        $table->transform('title', function($title, $i, $data) use($builder, $ids) {
            $link = $builder->editLink($title, $i, $data);
            if (isset($ids[$data['id']])) {
                $link = '<a class="active"' . substr($link, 2);
            }
            return $link;
        });

        // var_dump()

        $model = new ViewModel([
            'actions' => [],
            'list' => $table,
            'list_pager' => $result,
            'title' => $builder->getTitle(),

        ]);

        $model->setTemplate('kirkanta/entity/list.phtml');
        return $model;
    }

    public function readNotificationAction()
    {
        $repository = $this->entityInfo(Notification::class)->repository();
        $notification = $repository->findOneById($this->params('id'));
        $repository->readNotification($this->identity(), $notification);

        return [
            'notification' => $notification,
        ];
    }
}
