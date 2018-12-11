<?php

namespace SamuForm\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\View\Helper\HelperInterface as ViewHelperInterface;

interface HelperInterface extends ViewHelperInterface
{
    public function __invoke(ElementInterface $element = null);
    public function render(ElementInterface $element);
}
