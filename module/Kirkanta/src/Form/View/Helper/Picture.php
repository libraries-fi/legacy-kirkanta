<?php

namespace Kirkanta\Form\View\Helper;

use Exception;
use Zend\Form\ElementInterface;
use SamuForm\Form\View\Helper\AbstractHelper;

class Picture extends AbstractHelper
{
    public function __invoke(ElementInterface $element = null, $options = null)
    {
        if (!func_num_args()) {
            return $this;
        }

        if (!$element) {
            throw new Exception("Invalid element passed");
        }

        return $this->render($element);
    }

    public function render(ElementInterface $element)
    {
        $name = $element->getName();
        $filename = $element->getValue();
        $size = $this->getOption('picsize', 'small');
        $img = $this->getView()->plugin('KirkantaPicture')->tag($filename, $size);
        $html = sprintf('<figure class="kirkanta-form-picture size-%s">%s <figcaption>%s</figcaption> <input type="hidden" name="%s" value="%s"/> </figure>', $size, $img, $filename, $name, $filename);
        return $html;
    }
}
