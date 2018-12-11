<?php

namespace Kirkanta\Filter;

use ReflectionClass;
use Interop\Container\ContainerInterface;
use Kirkanta\PictureManager;
use Zend\Filter\AbstractFilter;
use Zend\Filter\FilterPluginManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class ScaleUploadedPicture extends AbstractFilter
{
    protected $options = ['sizes' => null];

    public static function create(ContainerInterface $container, array $options)
    {
        if (empty($options['sizes'])) {
            $sizes = $container->get('config')['kirkanta']['pictures']['sizes'];
            $options['sizes'] = array_keys($sizes);
        }
        return new static(
            $container->get('Kirkanta\PictureManager'),
            $options
        );
    }

    public function __construct(PictureManager $picture_manager, array $options)
    {
        $this->picture_manager = $picture_manager;
        $this->setOptions($options);
    }

    public function filter($value)
    {
        // RenameUpload filter replaces original tmp_name with target path
        if (is_array($value)) {
            if (empty($value['tmp_name'])) {
                return null;
            } else {
                $files = $this->picture_manager->resize($value['tmp_name'], $this->options['sizes']);
                return basename(reset($files));
            }
        }
        return $value;
    }
}
