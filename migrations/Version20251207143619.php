<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251207143619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_profile AS SELECT id, full_name, phone, blood_type, city, last_donation_date, available, created_at, user_id FROM user_profile');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('CREATE TABLE user_profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, phone VARCHAR(30) DEFAULT NULL, blood_type VARCHAR(3) DEFAULT NULL, city VARCHAR(80) NOT NULL, last_donation_date DATE DEFAULT NULL, available BOOLEAN NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_profile (id, full_name, phone, blood_type, city, last_donation_date, available, created_at, user_id) SELECT id, full_name, phone, blood_type, city, last_donation_date, available, created_at, user_id FROM __temp__user_profile');
        $this->addSql('DROP TABLE __temp__user_profile');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D95AB405A76ED395 ON user_profile (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_profile AS SELECT id, full_name, phone, blood_type, city, last_donation_date, available, created_at, user_id FROM user_profile');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('CREATE TABLE user_profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, phone VARCHAR(30) NOT NULL, blood_type VARCHAR(3) NOT NULL, city VARCHAR(80) NOT NULL, last_donation_date DATE DEFAULT NULL, available BOOLEAN NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_profile (id, full_name, phone, blood_type, city, last_donation_date, available, created_at, user_id) SELECT id, full_name, phone, blood_type, city, last_donation_date, available, created_at, user_id FROM __temp__user_profile');
        $this->addSql('DROP TABLE __temp__user_profile');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D95AB405A76ED395 ON user_profile (user_id)');
    }
}
