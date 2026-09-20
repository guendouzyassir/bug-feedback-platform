<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260912160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add explicit developer project assignments. Administrators grant access through project editing.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('project_developer');
        $table->addColumn('project_id', 'integer');
        $table->addColumn('user_id', 'integer');
        $table->setPrimaryKey(['project_id', 'user_id']);
        $table->addIndex(['project_id']);
        $table->addIndex(['user_id']);
        $table->addForeignKeyConstraint('project', ['project_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('app_user', ['user_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('project_developer');
    }
}
