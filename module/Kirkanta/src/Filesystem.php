<?php

namespace Kirkanta;

use Exception;
use Interop\Container\ContainerInterface;

class Filesystem
{
    const FILENAME_APPEND_ID = 1;
    const FILENAME_REPLACE_BASE = 2;

    protected $service_locator;
    protected $root;
    protected $web_root;

    public static function create(ContainerInterface $container)
    {
        $config = $container->get('config')['kirkanta']['filesystem']['user_files'];
        return new static($config);
    }

    public function __construct($config)
    {
        $this->config = $config + [
            /**
             * Domain with protocol (https://foobar.baz)
             */
            'host' => '',

            /**
             * Path to files root in the filesystem.
             */
            'root' => '',

            /**
             * Path to files for web browsers.
             */
            'web_root' => '',
        ];
    }

    public function getRoot()
    {
        return $this->config['root'];
    }

    public function getWebRoot()
    {
        return $this->config['web_root'];
    }

    public function getHost()
    {
        return $this->config['host'];
    }

    /**
     * Create a new file into the app's filesystem
     */
    public function writeFile($name, $contents)
    {

    }

    /**
     * Remove a file from the filesystem
     */
    public function removeFile($name)
    {

    }

    /**
     * Resolves a basename into absolute file path
     */
    public function getAbsoluteFilename($name)
    {
        return realpath($name);
    }

    public function storagePath($filename, $web_root = false)
    {
        if ($web_root) {
            $path = sprintf('%s/%s', $this->getWebRoot(), $filename);
        } else {
            $path = sprintf('%s/%s/%s', realpath('.'), $this->getRoot(), $filename);
        }

        return $path;
    }

    public function url($filename, $with_domain = false)
    {
        $url = $this->storagePath($filename, true);
        return $with_domain ? $this->getHost() . $url : $url;
    }

    /**
     * Generates a filename
     */
    public function filename($source, $method = self::FILENAME_APPEND_ID)
    {
        $maxlen = 60;
        $id = substr(uniqid(true), -8);
        $info = pathinfo($source);
        $filename = substr($info['filename'], 0, $maxlen - strlen($id . $info['extension']) - 1);
        $filename = sprintf('%s-%s.%s', $filename, $id, $info['extension']);
        return $info['dirname'] == '.' ? $filename : sprintf('%s/%s', $info['dirname'], $filename);
    }

    public function upload($data)
    {
        $filename = $this->filename($data['name']);
        $path = $this->storagePath($filename);
        if (!move_uploaded_file($data['tmp_name'], $path)) {
            throw new Exception("Failed to move uploaded file");
        }
        return $path;
    }
}
