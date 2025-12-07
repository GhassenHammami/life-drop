<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251207174232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blood_request ADD COLUMN updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__blood_request AS SELECT id, blood_type, hospital_name, city, units_needed, urgency, description, contact_phone, status, created_at, created_by_id FROM blood_request');
        $this->addSql('DROP TABLE blood_request');
        $this->addSql('CREATE TABLE blood_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, blood_type VARCHAR(3) NOT NULL, hospital_name VARCHAR(150) NOT NULL, city VARCHAR(80) NOT NULL, units_needed INTEGER NOT NULL, urgency VARCHAR(10) NOT NULL, description VARCHAR(255) DEFAULT NULL, contact_phone VARCHAR(30) NOT NULL, status VARCHAR(15) NOT NULL, created_at DATETIME NOT NULL, created_by_id INTEGER NOT NULL, CONSTRAINT FK_8431543EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO blood_request (id, blood_type, hospital_name, city, units_needed, urgency, description, contact_phone, status, created_at, created_by_id) SELECT id, blood_type, hospital_name, city, units_needed, urgency, description, contact_phone, status, created_at, created_by_id FROM __temp__blood_request');
        $this->addSql('DROP TABLE __temp__blood_request');
        $this->addSql('CREATE INDEX IDX_8431543EB03A8386 ON blood_request (created_by_id)');
    }
}
