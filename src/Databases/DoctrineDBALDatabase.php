<?php

namespace Sentience\DatabaseAdapters\Databases;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Sentience\Database\Dialects\MySQLDialect;
use Sentience\Database\Dialects\PgSQLDialect;
use Sentience\Database\Dialects\SQLDialect;
use Sentience\Database\Dialects\SQLiteDialect;
use Sentience\Database\Dialects\SQLServerDialect;
use Sentience\Database\Driver;
use Sentience\DatabaseAdapters\Adapters\DoctrineDBALAdapter;

class DoctrineDBALDatabase extends \Sentience\Database\Databases\DatabaseAbstract
{
    public static function connection(Connection $connection, array $options = []): static
    {
        return new static($connection, $options);
    }

    public function __construct(Connection $connection, array $options = [])
    {
        $adapter = new DoctrineDBALAdapter($connection);

        $dialect = match (($connection->getDatabasePlatform())::class) {
            MySQLPlatform::class => new MySQLDialect(Driver::MYSQL, $adapter->version(), $options),
            PostgreSQLPlatform::class => new PgSQLDialect(Driver::MYSQL, $adapter->version(), $options),
            SQLitePlatform::class => new SQLiteDialect(Driver::MYSQL, $adapter->version(), $options),
            SQLServerPlatform::class => new SQLServerDialect(Driver::MYSQL, $adapter->version(), $options),
            default => new SQLDialect(Driver::PGSQL, $adapter->version(), $options)
        };

        parent::__construct($adapter, $dialect);
    }
}
