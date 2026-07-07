<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707092940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE app_user (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              email VARCHAR(180) NOT NULL,
              roles CLOB NOT NULL,
              password VARCHAR(255) NOT NULL,
              full_name VARCHAR(120) NOT NULL,
              is_active BOOLEAN NOT NULL,
              created_at DATETIME NOT NULL
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON app_user (email)');
        $this->addSql(<<<'SQL'
            CREATE TABLE bug_comment (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              content CLOB NOT NULL,
              created_at DATETIME NOT NULL,
              bug_report_id INTEGER NOT NULL,
              author_id INTEGER NOT NULL,
              CONSTRAINT FK_CE4350DC41193163 FOREIGN KEY (bug_report_id) REFERENCES bug_report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_CE4350DCF675F31B FOREIGN KEY (author_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_CE4350DC41193163 ON bug_comment (bug_report_id)');
        $this->addSql('CREATE INDEX IDX_CE4350DCF675F31B ON bug_comment (author_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE bug_report (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              title VARCHAR(180) NOT NULL,
              description CLOB NOT NULL,
              steps_to_reproduce CLOB DEFAULT NULL,
              expected_result CLOB DEFAULT NULL,
              actual_result CLOB DEFAULT NULL,
              priority VARCHAR(255) NOT NULL,
              status VARCHAR(255) NOT NULL,
              screenshot_filename VARCHAR(255) DEFAULT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              closed_at DATETIME DEFAULT NULL,
              project_id INTEGER NOT NULL,
              reporter_id INTEGER NOT NULL,
              assigned_developer_id INTEGER DEFAULT NULL,
              CONSTRAINT FK_F6F2DC7A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_F6F2DC7AE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_F6F2DC7A8642D293 FOREIGN KEY (assigned_developer_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_F6F2DC7A166D1F9C ON bug_report (project_id)');
        $this->addSql('CREATE INDEX IDX_F6F2DC7AE1CFE6F5 ON bug_report (reporter_id)');
        $this->addSql('CREATE INDEX IDX_F6F2DC7A8642D293 ON bug_report (assigned_developer_id)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_STATUS ON bug_report (status)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_PRIORITY ON bug_report (priority)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_CREATED_AT ON bug_report (created_at)');
        $this->addSql(<<<'SQL'
            CREATE TABLE project (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(120) NOT NULL,
              description CLOB DEFAULT NULL,
              platform VARCHAR(60) NOT NULL,
              is_active BOOLEAN NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              body CLOB NOT NULL,
              headers CLOB NOT NULL,
              queue_name VARCHAR(190) NOT NULL,
              created_at DATETIME NOT NULL,
              available_at DATETIME NOT NULL,
              delivered_at DATETIME DEFAULT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (
              queue_name, available_at, delivered_at,
              id
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE bug_comment');
        $this->addSql('DROP TABLE bug_report');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
