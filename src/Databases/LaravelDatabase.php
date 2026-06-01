<?php

namespace Sentience\DatabaseAdapters\Databases;

use Illuminate\Database\Connection;
use Sentience\Database\Databases\DatabaseAbstract;
use Sentience\Database\Dialects\MySQLDialect;
use Sentience\Database\Dialects\PgSQLDialect;
use Sentience\Database\Dialects\SQLDialect;
use Sentience\Database\Dialects\SQLiteDialect;
use Sentience\Database\Dialects\SQLServerDialect;
use Sentience\Database\Driver;
use Sentience\DatabaseAdapters\Adapters\LaravelAdapter;

class LaravelDatabase extends DatabaseAbstract
{
    public static function connection(Connection $connection, array $options = []): static
    {
        return new static($connection, $options);
    }

    public function __construct(Connection $connection, array $options = [])
    {
        $adapter = new LaravelAdapter($connection);

        $driver = $connection->getDriverName();

        $dialect = match ($driver) {
            'mariadb' => new MySQLDialect(Driver::MARIADB, $adapter->version(), $options),
            'mysql' => new MySQLDialect(Driver::MYSQL, $adapter->version(), $options),
            'pgsql' => new PgSQLDialect(Driver::PGSQL, $adapter->version(), $options),
            'sqlite' => new SQLiteDialect(Driver::SQLITE, $adapter->version(), $options),
            'sqlsrv' => new SQLServerDialect(Driver::SQLSRV, $adapter->version(), $options),
            default => new SQLDialect(Driver::PGSQL, $adapter->version(), $options)
        };

        parent::__construct($adapter, $dialect);
    }
}
