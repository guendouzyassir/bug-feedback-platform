<?php

namespace App\Tests\Migrations;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use DoctrineMigrations\Version20260912160000;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ProjectDeveloperMigrationTest extends TestCase
{
    public function testMigrationPreservesExistingDataAndCleansUpMembershipsOnDeletion(): void
    {
        require_once dirname(__DIR__, 2).'/migrations/Version20260912160000.php';
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('PRAGMA foreign_keys = ON');
        $schema = new Schema();
        foreach (['project', 'app_user'] as $name) {
            $table = $schema->createTable($name);
            $table->addColumn('id', 'integer');
            $table->setPrimaryKey(['id']);
        }
        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }
        $connection->insert('project', ['id' => 1]);
        $connection->insert('app_user', ['id' => 1]);

        $before = $connection->createSchemaManager()->introspectSchema();
        $after = clone $before;
        (new Version20260912160000($connection, new NullLogger()))->up($after);
        $diff = $connection->createSchemaManager()->createComparator()->compareSchemas($before, $after);
        foreach ($connection->getDatabasePlatform()->getAlterSchemaSQL($diff) as $sql) {
            $connection->executeStatement($sql);
        }

        self::assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM project'));
        self::assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM app_user'));
        self::assertSame(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM project_developer'));
        $connection->insert('project_developer', ['project_id' => 1, 'user_id' => 1]);
        $connection->delete('app_user', ['id' => 1]);
        self::assertSame(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM project_developer'));
        $connection->close();
    }
}
