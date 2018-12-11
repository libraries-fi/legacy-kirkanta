<?php

namespace Kirkanta\Ptv\Util;

use UnexpectedValueException;

class Municipalities
{
    const SOURCE_PATH = __DIR__ . '/../../data/municipalities.txt';

    protected $data;

    protected function load()
    {
        if (is_array($this->data)) {
            return;
        }

        $this->data = [];
        $data = file(self::SOURCE_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // First line is the data source URL.
        array_shift($data);

        foreach ($data as $row) {
            list($id, $name) = explode("\t", $row);
            $this->data[$name] = $id;
        }

        return $this->data;
    }

    public function idToName($id)
    {
        $this->load();

        if ($name = array_search($id)) {
            return $name;
        }

        throw new UnexpectedValueException(sprintf('ID \'%d\' not found', $id));

    }

    public function nameToId($name)
    {
        $this->load();

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        throw new UnexpectedValueException(sprintf('Invalid municipality \'%s\'', $name));
    }
}
