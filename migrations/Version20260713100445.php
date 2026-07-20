<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713100445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__client_profile AS SELECT id, company_name, phone_number, company_address FROM client_profile');
        $this->addSql('DROP TABLE client_profile');
        $this->addSql('CREATE TABLE client_profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, company_name VARCHAR(180) NOT NULL, phone_number VARCHAR(30) DEFAULT NULL, company_address CLOB DEFAULT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_D36AEE72A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO client_profile (id, company_name, phone_number, company_address) SELECT id, company_name, phone_number, company_address FROM __temp__client_profile');
        $this->addSql('DROP TABLE __temp__client_profile');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D36AEE72A76ED395 ON client_profile (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__client_profile AS SELECT id, company_name, phone_number, company_address FROM client_profile');
        $this->addSql('DROP TABLE client_profile');
        $this->addSql('CREATE TABLE client_profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, company_name VARCHAR(180) NOT NULL, phone_number VARCHAR(30) DEFAULT NULL, company_address CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO client_profile (id, company_name, phone_number, company_address) SELECT id, company_name, phone_number, company_address FROM __temp__client_profile');
        $this->addSql('DROP TABLE __temp__client_profile');
    }
}
