<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206211753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blood_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, blood_type VARCHAR(3) NOT NULL, hospital_name VARCHAR(150) NOT NULL, city VARCHAR(80) NOT NULL, units_needed INTEGER NOT NULL, urgency VARCHAR(10) NOT NULL, description VARCHAR(255) DEFAULT NULL, contact_phone VARCHAR(30) NOT NULL, status VARCHAR(15) NOT NULL, created_at DATETIME NOT NULL, created_by_id INTEGER DEFAULT NULL, CONSTRAINT FK_8431543EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8431543EB03A8386 ON blood_request (created_by_id)');
        $this->addSql('CREATE TABLE donation_offer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, message CLOB DEFAULT NULL, status VARCHAR(15) NOT NULL, created_at DATETIME NOT NULL, request_id INTEGER NOT NULL, donor_id INTEGER NOT NULL, CONSTRAINT FK_F27CDDC3427EB8A5 FOREIGN KEY (request_id) REFERENCES blood_request (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F27CDDC33DD7B7A7 FOREIGN KEY (donor_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F27CDDC3427EB8A5 ON donation_offer (request_id)');
        $this->addSql('CREATE INDEX IDX_F27CDDC33DD7B7A7 ON donation_offer (donor_id)');
        $this->addSql('CREATE TABLE donor_profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, phone VARCHAR(30) NOT NULL, blood_type VARCHAR(3) NOT NULL, city VARCHAR(80) NOT NULL, last_donation_date DATE DEFAULT NULL, available BOOLEAN NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_F7DE98B8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F7DE98B8A76ED395 ON donor_profile (user_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE blood_request');
        $this->addSql('DROP TABLE donation_offer');
        $this->addSql('DROP TABLE donor_profile');
        $this->addSql('DROP TABLE user');
    }
}
