<?php

namespace Kirkanta\Ptv;

use Exception;

class SynchronizationException extends Exception
{
    private $document;
    
    public function setPtvDocument(array $document)
    {
        $this->document = $document;
    }

    public function getPtvDocument()
    {
        return $this->document;
    }
}
