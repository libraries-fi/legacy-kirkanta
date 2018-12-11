<?php

namespace Kirkanta\View\Helper;

use Kirkanta\PictureManager;
use Zend\View\Helper\AbstractHelper;
use Zend\View\HelperPluginManager;

class Picture extends AbstractHelper
{
    private $pictures;

    public static function create(HelperPluginManager $plugins)
    {
        return new static(
            $plugins->getServiceLocator()->get('Kirkanta\PictureManager')
        );
    }

    public function __construct(PictureManager $pictures)
    {
        $this->pictures = $pictures;
    }

    public function getPictureManager()
    {
        return $this->pictures;
    }

    public function __invoke($name = null, $size = 'small')
    {
        if (func_num_args() == 2) {
            return $this->tag($name, $size);
        } else {
            return $this;
        }
    }

    public function url($name, $size)
    {
        return $this->getPictureManager()->urlForImage($name, $size);
    }

    public function tag($name, $size)
    {
        $url = $this->url($name, $size);
        list($url, $name, $size) = array_map('htmlspecialchars', [$url, $name, $size]);
        return sprintf('<img src="%s" alt="%s %s"/>', $url, $name, $size);
    }
}
