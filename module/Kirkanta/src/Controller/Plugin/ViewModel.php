<?php

namespace Kirkanta\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel as View_ViewModel;

class ViewModel extends AbstractPlugin
{
    protected $types = [
        'html' => View_ViewModel::class,
        'json' => JsonModel::class,
    ];

    protected $model;
    protected $child;

    public function __invoke($type = null)
    {
        if ($type || !$this->model) {
            $this->model = $this->create($type);
        }
        return $this;
    }

    public function model()
    {
        return $this->model;
    }

    public function terminal($state = true) {
        $this->model->setTerminal($state);
        return $this;
    }

    public function create($type)
    {
        if (is_null($type)) {
            $type = $this->getController()->getRequest()->isXmlHttpRequest() ? 'json' : 'html';
        }

        $class = $this->types[$type];
        return new $class;
    }

    public function template($template)
    {
        $this->model->setTemplate($template);
        return $this;
    }

    public function data(array $data)
    {
        $this->model->setVariables($data);
        return $this;
    }

    public function child($name, $type = 'html')
    {
        $child = new $this;
        $child->model = $this->create($type);
        $this->model->addChild($child->model, $name, true);
        return $child;
    }
}
