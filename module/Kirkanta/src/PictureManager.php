<?php

namespace Kirkanta;

use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Interop\Container\ContainerInterface;

class PictureManager
{
    private $filesystem;
    private $imagine;
    private $sizes;

    public static function create(ContainerInterface $container)
    {
        $config = $container->get('config')['kirkanta']['pictures'];
        return new static(
            $container->get('Kirkanta\Filesystem'),
            $container->get('Imagine'),
            $config
        );
    }

    public function __construct(Filesystem $file_system, ImagineInterface $imagine, array $config)
    {
        $this->filesystem = $file_system;
        $this->imagine = $imagine;
        $this->config = $config + [
            'root' => '',
            'sizes' => [],
        ];
    }

    public function getFileSystem()
    {
        return $this->filesystem;
    }

    public function getSizes()
    {
        return $this->config['sizes'];
    }

    public function getSize($id)
    {
        if (!isset($this->config['sizes'][$id])) {
            throw new Exception(sprintf('Invalid size %s', $id));
        }
        return $this->config['sizes'][$id];
    }

    public function pathForImage($filename, $size)
    {
        $filename = sprintf('%s/%s/%s', $this->config['root'], $size, $filename);
        return $this->getFilesystem()->storagePath($filename, false);
    }

    public function urlForImage($filename, $size, $with_domain = false)
    {
        $filename = sprintf('%s/%s/%s', $this->config['root'], $size, $filename);
        return $this->getFilesystem()->url($filename, $with_domain);
    }

    public function upload($data)
    {
        $data['name'] = 'images/original/' . $data['name'];
        $filename = $this->filesystem->upload($data);
        $files = $this->resize($filename);
        return basename(reset($files));
    }

    public function resize($source, $sizes = null)
    {
        $sizes = array_reverse($sizes ?: array_keys($this->getSizes()));
        $basename = mb_strtolower(basename($source));
        $basename = preg_replace('/[\s_]+/', '-', $basename);
        $image = $this->imagine->open($source);
        $files = [];

        foreach ($sizes as $size_id) {
            $size = $this->getSize($size_id);
            $sizename = sprintf('images/%s/%s', $size_id, $basename);
            $filename = $this->filesystem->storagePath($sizename);
            $resize = $this->scaleSizeForImage($image, new Box($size[0], $size[1]));
            $image->resize($resize);
            $image->save($filename);
            $files[$size_id] = $filename;
        }

        return $files;
    }

    /**
     * Return a size that conforms to image's aspect ratio
     *
     * @param $image Image to scale the size to
     * @param $size Maximum bounding box
     * @return BoxInterface
     */
    public function scaleSizeForImage(ImageInterface $image, BoxInterface $max)
    {
        $orig = $image->getSize();
        $r0 = $orig->getWidth() / $orig->getHeight();
        $r1 = $max->getWidth() / $max->getHeight();

        if ($r0 < $r1) {
            $height = $orig->getHeight() * ($max->getWidth() / $orig->getWidth());
            $new_size = new Box($max->getWidth(), $height);
        } else {
            $width = $orig->getWidth() * ($max->getHeight() / $orig->getHeight());
            $new_size = new Box($width, $max->getHeight());
        }

        return $new_size;
    }
}
