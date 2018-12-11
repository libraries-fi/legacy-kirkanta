<?php

namespace Kirkanta\Logging;

use Doctrine\DBAL\Logging\SQLLogger;

class DoctrineLogger implements SQLLogger
{

    private $file;
    private $first_run = true;
    private $buffer = [];

    public function __destruct()
    {
        if (!empty($this->buffer)) {
            $this->log("----- FINISHED -----");
        }

        $this->flush();
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->log($sql, $params);
    }

    public function stopQuery()
    {

    }

    public function flush()
    {
        if (empty($this->buffer)) {
            return;
        }

        $data = implode(PHP_EOL, $this->buffer) . PHP_EOL;
        $this->buffer = [];

        file_put_contents($this->getFile(), $data, FILE_APPEND);
    }

    protected function log($row, $params = null)
    {
        if ($this->getFile()) {
            if ($this->first_run) {
                $this->first_run = false;
                $this->log("\n\n\n\n\n\n\n");
                $this->log("----- STARTED -----");
            }

            $stamp = date('Y-m-d H:i:s');
            $row = "[{$stamp}] {$row}";
            $this->buffer[] = $row;

            if ($params) {
                $this->buffer[] = print_r($params, true);
            }
        }
    }
}
