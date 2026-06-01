<?php

namespace Sentience\DatabaseAdapters\Adapters;

use Throwable;
use Illuminate\Database\Connection;
use Sentience\Database\Adapters\AdapterAbstract;
use Sentience\Database\Dialects\DialectInterface;
use Sentience\Database\Queries\Objects\QueryWithParams;
use Sentience\Database\Results\ResultInterface;
use Sentience\DatabaseAdapters\Results\LaravelResult;

class LaravelAdapter extends AdapterAbstract
{
    public function __construct(
        protected Connection $connection,
    ) {
    }

    public function version(): string
    {
        return $this->connection->getDatabaseVersion();
    }

    public function exec(string $query): void
    {
        $this->connection->statement($query);
    }

    public function query(string $query): ResultInterface
    {
        $result = $this->connection->select($query);

        return new LaravelResult($result);
    }

    public function queryWithParams(DialectInterface $dialect, QueryWithParams $queryWithParams, bool $emulatePrepare): ResultInterface
    {
        $queryWithParams->namedParamsToQuestionMarks();

        $result = $this->connection->select(
            $queryWithParams->query,
            $queryWithParams->params
        );

        return new LaravelResult($result);
    }

    public function beginTransaction(DialectInterface $dialect, ?string $name = null): void
    {
        $this->connection->beginTransaction();
    }

    public function commitTransaction(DialectInterface $dialect, ?string $name = null): void
    {
        $this->connection->commit();
    }

    public function rollbackTransaction(DialectInterface $dialect, ?string $name = null): void
    {
        $this->connection->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    public function lastInsertId(?string $name = null): null|int|string
    {
        try {
            $id = $this->connection->getPdo()->lastInsertId($name);

            if (empty($id)) {
                return null;
            }

            return is_numeric($id) ? (int) $id : $id;
        } catch (Throwable $exception) {
            return null;
        }
    }

    public static function extensionsInstalled(): bool
    {
        return class_exists(Connection::class);
    }
}
