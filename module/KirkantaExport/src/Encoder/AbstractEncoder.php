<?php

namespace Kirkanta\Export\Encoder;

abstract class AbstractEncoder implements EncoderInterface
{
    protected $mime;
    protected $callback;
    protected $labels;
    protected $group_by;

    public function __construct(array $labels = [])
    {
        $this->labels = $labels;
    }

    public function getMimeType()
    {
        return $this->mime;
    }

    public function transform(callable $callback)
    {
        $this->callback = $callback;
    }

    public function groupBy($column)
    {
        $this->group_by = $column;
    }

    public function getGroupBy()
    {
        return $this->group_by;
    }

    public function getLabels()
    {
        return $this->labels;
    }
}
