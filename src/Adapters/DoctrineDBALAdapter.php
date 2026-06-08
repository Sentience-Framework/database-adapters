<?php

namespace Sentience\DatabaseAdapters\Adapters;

use PDO;
use SQLite3;
use Throwable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Sentience\Database\Adapters\AdapterAbstract;
use Sentience\Database\Dialects\DialectInterface;
use Sentience\Database\Queries\Objects\QueryWithParams;
use Sentience\Database\Results\ResultInterface;
use Sentience\DatabaseAdapters\Results\DoctrineDBALResult;

class DoctrineDBALAdapter extends AdapterAbstract
{
    public function __construct(protected Connection $connection)
    {
        $nativeConnection = $connection->getNativeConnection();

        if ($nativeConnection instanceof SQLite3) {
            $nativeConnection->createFunction(
                static::REGEXP_FUNCTION,
                fn(string $value, string $pattern): bool => $this->regexpFunction(
                    $value,
                    $pattern
                ),
                2
            );

            $nativeConnection->createFunction(
                static::REGEXP_LIKE_FUNCTION,
                fn(string $value, string $pattern, string $flags = ''): bool => $this->regexpLikeFunction(
                    $value,
                    $pattern,
                    $flags
                )
            );
        }

        if ($nativeConnection instanceof PDO) {
            foreach (['sqliteCreateFunction', 'createFunction'] as $method) {
                if (method_exists($nativeConnection, $method)) {
                    [$nativeConnection, $method](
                        static::REGEXP_FUNCTION,
                        fn(string $value, string $pattern): bool => $this->regexpFunction(
                            $value,
                            $pattern
                        ),
                        2
                    );

                    [$nativeConnection, $method](
                        static::REGEXP_LIKE_FUNCTION,
                        fn(string $value, string $pattern, string $flags = ''): bool => $this->regexpLikeFunction(
                            $value,
                            $pattern,
                            $flags
                        )
                    );
                }
            }
        }
    }

    public function version(): string
    {
        return $this->connection->getServerVersion();
    }

    public function exec(string $query): void
    {
        $this->connection->executeStatement($query);
    }

    public function query(string $query): ResultInterface
    {
        $result = $this->connection->executeQuery($query);

        return new DoctrineDBALResult($result);
    }

    public function queryWithParams(DialectInterface $dialect, QueryWithParams $queryWithParams, bool $emulatePrepare): ResultInterface
    {
        if ($emulatePrepare) {
            return $this->query($queryWithParams->toSql($dialect));
        }

        $queryWithParams->namedParamsToQuestionMarks();

        $result = $this->connection->executeQuery(
            $queryWithParams->query,
            $queryWithParams->params,
            array_map(
                fn(null|bool|int|float|string $param): mixed => match (get_debug_type($param)) {
                    'null' => ParameterType::NULL,
                    'bool' => ParameterType::BOOLEAN,
                    'int' => ParameterType::INTEGER,
                    default => ParameterType::STRING,
                },
                $queryWithParams->params
            )
        );

        return new DoctrineDBALResult($result);
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

    public function beginSavepoint(DialectInterface $dialect, string $name): void
    {
        $this->connection->createSavepoint($name);
    }

    public function commitSavepoint(DialectInterface $dialect, string $name): void
    {
        $this->connection->releaseSavepoint($name);
    }

    public function rollbackSavepoint(DialectInterface $dialect, string $name): void
    {
        $this->connection->rollbackSavepoint($name);
    }

    public function inTransaction(): bool
    {
        return $this->connection->isTransactionActive();
    }

    public function lastInsertId(?string $name = null): null|int|string
    {
        try {
            $id = $this->connection->lastInsertId();

            if (empty($id)) {
                return null;
            }

            return is_numeric($id) ? (int) $id : $id;
        } catch (Throwable $exception) {
            return null;
        }
    }
}
