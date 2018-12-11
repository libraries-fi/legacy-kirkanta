<?php

namespace Kirkanta\View\Helper;

use DateTime;
use Zend\View\Helper\AbstractHelper;

class FormatDateTime extends FormatDate
{
    protected $format = 'Y-m-d H:i';
}
