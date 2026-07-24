<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260724123437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__bug_comment AS SELECT id, content, created_at, bug_report_id, author_id FROM bug_comment');
        $this->addSql('DROP TABLE bug_comment');
        $this->addSql('CREATE TABLE bug_comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, bug_report_id INTEGER NOT NULL, author_id INTEGER DEFAULT NULL, CONSTRAINT FK_CE4350DC41193163 FOREIGN KEY (bug_report_id) REFERENCES bug_report (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CE4350DCF675F31B FOREIGN KEY (author_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO bug_comment (id, content, created_at, bug_report_id, author_id) SELECT id, content, created_at, bug_report_id, author_id FROM __temp__bug_comment');
        $this->addSql('DROP TABLE __temp__bug_comment');
        $this->addSql('CREATE INDEX IDX_CE4350DCF675F31B ON bug_comment (author_id)');
        $this->addSql('CREATE INDEX IDX_CE4350DC41193163 ON bug_comment (bug_report_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__bug_comment AS SELECT id, content, created_at, bug_report_id, author_id FROM bug_comment');
        $this->addSql('DROP TABLE bug_comment');
        $this->addSql('CREATE TABLE bug_comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, bug_report_id INTEGER NOT NULL, author_id INTEGER NOT NULL, CONSTRAINT FK_CE4350DC41193163 FOREIGN KEY (bug_report_id) REFERENCES bug_report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CE4350DCF675F31B FOREIGN KEY (author_id) REFERENCES app_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO bug_comment (id, content, created_at, bug_report_id, author_id) SELECT id, content, created_at, bug_report_id, author_id FROM __temp__bug_comment');
        $this->addSql('DROP TABLE __temp__bug_comment');
        $this->addSql('CREATE INDEX IDX_CE4350DC41193163 ON bug_comment (bug_report_id)');
        $this->addSql('CREATE INDEX IDX_CE4350DCF675F31B ON bug_comment (author_id)');
    }
}
