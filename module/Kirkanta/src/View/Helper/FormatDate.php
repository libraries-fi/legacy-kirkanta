<?php

namespace Kirkanta\View\Helper;

use DateTime;
use Zend\View\Helper\AbstractHelper;

class FormatDate extends AbstractHelper
{
    protected $format = 'Y-m-d';

    public function __invoke(DateTime $time = null, $format = null)
    {
        if (!func_num_args()) {
            return $this;
        }

        return $this->format($time, $format);
    }

    public function format(Datetime $time = null, $format = null)
    {
        if (is_null($time)) {
            return '';
        }

        return $time->format($format ?: $this->format);
    }
}
