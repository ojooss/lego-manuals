<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240225072113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manual ADD COLUMN file BLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE manual ADD COLUMN cover BLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__manual AS SELECT id, set_id, filename, covername, url FROM manual');
        $this->addSql('DROP TABLE manual');
        $this->addSql('CREATE TABLE manual (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, set_id INTEGER NOT NULL, filename VARCHAR(255) NOT NULL, covername VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_10DBBEC410FB0D18 FOREIGN KEY (set_id) REFERENCES "set" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO manual (id, set_id, filename, covername, url) SELECT id, set_id, filename, covername, url FROM __temp__manual');
        $this->addSql('DROP TABLE __temp__manual');
        $this->addSql('CREATE INDEX IDX_10DBBEC410FB0D18 ON manual (set_id)');
    }
}
