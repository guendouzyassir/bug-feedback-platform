<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707113920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__bug_report AS SELECT id, title, description, steps_to_reproduce, expected_result, actual_result, priority, status, screenshot_filename, created_at, updated_at, closed_at, project_id, reporter_id, assigned_developer_id FROM bug_report');
        $this->addSql('DROP TABLE bug_report');
        $this->addSql('CREATE TABLE bug_report (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(180) NOT NULL, description CLOB NOT NULL, steps_to_reproduce CLOB DEFAULT NULL, expected_result CLOB DEFAULT NULL, actual_result CLOB DEFAULT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, screenshot_filename VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, project_id INTEGER NOT NULL, reporter_id INTEGER NOT NULL, assigned_developer_id INTEGER DEFAULT NULL, opened_at DATETIME DEFAULT NULL, treated_at DATETIME DEFAULT NULL, CONSTRAINT FK_F6F2DC7A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F6F2DC7AE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES app_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F6F2DC7A8642D293 FOREIGN KEY (assigned_developer_id) REFERENCES app_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO bug_report (id, title, description, steps_to_reproduce, expected_result, actual_result, priority, status, screenshot_filename, created_at, updated_at, closed_at, project_id, reporter_id, assigned_developer_id) SELECT id, title, description, steps_to_reproduce, expected_result, actual_result, priority, status, screenshot_filename, created_at, updated_at, closed_at, project_id, reporter_id, assigned_developer_id FROM __temp__bug_report');
        $this->addSql('DROP TABLE __temp__bug_report');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_CREATED_AT ON bug_report (created_at)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_PRIORITY ON bug_report (priority)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_STATUS ON bug_report (status)');
        $this->addSql('CREATE INDEX IDX_F6F2DC7A8642D293 ON bug_report (assigned_developer_id)');
        $this->addSql('CREATE INDEX IDX_F6F2DC7AE1CFE6F5 ON bug_report (reporter_id)');
        $this->addSql('CREATE INDEX IDX_F6F2DC7A166D1F9C ON bug_report (project_id)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_OPENED_AT ON bug_report (opened_at)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_TREATED_AT ON bug_report (treated_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__bug_report AS SELECT id, title, description, steps_to_reproduce, expected_result, actual_result, priority, status, screenshot_filename, created_at, updated_at, closed_at, project_id, reporter_id, assigned_developer_id FROM bug_report');
        $this->addSql('DROP TABLE bug_report');
        $this->addSql('CREATE TABLE bug_report (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(180) NOT NULL, description CLOB NOT NULL, steps_to_reproduce CLOB DEFAULT NULL, expected_result CLOB DEFAULT NULL, actual_result CLOB DEFAULT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, screenshot_filename VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, project_id INTEGER NOT NULL, reporter_id INTEGER NOT NULL, assigned_developer_id INTEGER DEFAULT NULL, CONSTRAINT FK_F6F2DC7A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F6F2DC7AE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F6F2DC7A8642D293 FOREIGN KEY (assigned_developer_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO bug_report (id, title, description, steps_to_reproduce, expected_result, actual_result, priority, status, screenshot_filename, created_at, updated_at, closed_at, project_id, reporter_id, assigned_developer_id) SELECT id, title, description, steps_to_reproduce, expected_result, actual_result, priority, status, screenshot_filename, created_at, updated_at, closed_at, project_id, reporter_id, assigned_developer_id FROM __temp__bug_report');
        $this->addSql('DROP TABLE __temp__bug_report');
        $this->addSql('CREATE INDEX IDX_F6F2DC7A166D1F9C ON bug_report (project_id)');
        $this->addSql('CREATE INDEX IDX_F6F2DC7AE1CFE6F5 ON bug_report (reporter_id)');
        $this->addSql('CREATE INDEX IDX_F6F2DC7A8642D293 ON bug_report (assigned_developer_id)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_STATUS ON bug_report (status)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_PRIORITY ON bug_report (priority)');
        $this->addSql('CREATE INDEX IDX_BUG_REPORT_CREATED_AT ON bug_report (created_at)');
    }
}
