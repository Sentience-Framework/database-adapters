<?php

namespace Sentience\DatabaseAdapters\Results;

use stdClass;
use Sentience\Database\Results\ResultAbstract;

class LaravelResult extends ResultAbstract
{
    protected int $index = 0;

    public function __construct(protected array $rows)
    {
        $this->rows = $rows;
    }

    public function columns(): array
    {
        if (empty($this->rows)) {
            return [];
        }

        $firstRow = $this->rows[0];
        $columns = [];

        foreach ($firstRow as $name => $value) {
            $columns[$name] = null;
        }

        return $columns;
    }

    public function fetchAssoc(): ?array
    {
        if ($this->index >= count($this->rows)) {
            return null;
        }

        $row = $this->rows[$this->index++];

        return (array) $row;
    }

    public function fetchAssocs(): array
    {
        $results = [];

        foreach ($this->rows as $row) {
            $results[] = (array) $row;
        }

        return $results;
    }
}
