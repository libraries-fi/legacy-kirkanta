<?php

namespace Kirkanta\Export\Encoder;

class Csv extends AbstractEncoder
{
    protected $mime = 'text/csv';

    public function encode(array $data)
    {
        $columns = $this->isAssociative($data) ? array_keys($data) : null;
        $document = '';
        $last_group = null;

        if ($this->getLabels()) {
            $document = $this->encodeRow($this->getLabels()) . PHP_EOL . PHP_EOL;
        }

        foreach ($data as $row) {
            if ($this->getGroupBy()) {
                if (($group = $row[$this->getGroupBy()]) != $last_group) {
                    $document .= PHP_EOL . $this->encodeRow([$group]) . PHP_EOL;
                    $last_group = $group;
                }

            }
            if ($this->callback) {
                $row = call_user_func($this->callback, $row);
            }
            if ($columns) {
                $row = array_filter($row, function($value, $key) { return in_array($key, $columns, true); });
            }
            $document .= $this->encodeRow($row) . PHP_EOL;
        }

        return (object)[
            'type' => 'csv',
            'mime' => $this->getMimeType(),
            'data' => $document,
        ];
    }

    private function encodeRow(array $row)
    {
        $data = array_map(function($value) {
            $value = utf8_decode($value);
            return sprintf('"%s"', str_replace(['"', '\''], ['""', '\'\''], $value));
        }, $row);
        return implode(";", $data);
    }

    private function isAssociative(array $array)
    {
        return array_keys($array) != range(0, count($array) - 1);
    }
}
