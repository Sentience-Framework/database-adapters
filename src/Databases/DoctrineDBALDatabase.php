<?php

namespace Sentience\DatabaseAdapters\Databases;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Sentience\Database\Databases\DatabaseAbstract;
use Sentience\Database\Dialects\MySQLDialect;
use Sentience\Database\Dialects\OCIDialect;
use Sentience\Database\Dialects\PgSQLDialect;
use Sentience\Database\Dialects\SQLDialect;
use Sentience\Database\Dialects\SQLiteDialect;
use Sentience\Database\Dialects\SQLServerDialect;
use Sentience\Database\Driver;
use Sentience\DatabaseAdapters\Adapters\DoctrineDBALAdapter;

class DoctrineDBALDatabase extends DatabaseAbstract
{
    public static function connection(Connection $connection, array $options = []): static
    {
        return new static($connection, $options);
    }

    public function __construct(Connection $connection, array $options = [])
    {
        $adapter = new DoctrineDBALAdapter($connection);

        $dialect = match (($connection->getDatabasePlatform())::class) {
            MariaDBPlatform::class => new MySQLDialect(Driver::MARIADB, $adapter->version(), $options),
            MySQLPlatform::class => new MySQLDialect(Driver::MYSQL, $adapter->version(), $options),
            OraclePlatform::class => new OCIDialect(Driver::OCI, $adapter->version(), $options),
            PostgreSQLPlatform::class => new PgSQLDialect(Driver::PGSQL, $adapter->version(), $options),
            SQLitePlatform::class => new SQLiteDialect(Driver::SQLITE, $adapter->version(), $options),
            SQLServerPlatform::class => new SQLServerDialect(Driver::SQLSRV, $adapter->version(), $options),
            default => new SQLDialect(Driver::PGSQL, $adapter->version(), $options)
        };

        parent::__construct($adapter, $dialect);
    }
}
