<?php

namespace Sentience\DatabaseAdapters\Results;

use Doctrine\DBAL\Result;
use Sentience\Database\Results\ResultAbstract;

class DoctrineDBALResult extends ResultAbstract
{
    public function __construct(protected Result $result)
    {
    }

    public function columns(): array
    {
        $columns = [];

        for ($i = 0; $i < $this->result->columnCount(); $i++) {
            $name = $this->result->getColumnName($i);

            // Cannot retrieve native type via DBALResult
            $columns[$name] = null;
        }

        return $columns;
    }

    public function fetchAssoc(): ?array
    {
        $row = $this->result->fetchAssociative();

        if (is_bool($row)) {
            return null;
        }

        return $row;
    }

    public function fetchAssocs(): array
    {
        return $this->result->fetchAllAssociative();
    }
}
