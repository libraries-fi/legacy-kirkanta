<?php

namespace Kirkanta\View\Helper;

use Zend\View\Helper\AbstractHelper;

class AssetUrl extends AbstractHelper
{
  public function __invoke($path = null)
  {
    if (!func_num_args()) {
      return $this;
    }
    return $this->url($path);
  }

  public function url($path)
  {
    $url = $this->getView()->basePath($path);
    $mtime = filemtime(sprintf('%s/%s', $_SERVER['DOCUMENT_ROOT'], $path));
    return sprintf('%s?%d', $url, $mtime);
  }
}
