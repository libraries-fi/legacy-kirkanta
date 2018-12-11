<?php

namespace Kirkanta\Export\Encoder;

interface EncoderInterface
{
    public function encode(array $data);
    public function getMimeType();
}
