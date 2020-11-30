<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201129202828 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manual (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, set_id INTEGER NOT NULL, filename VARCHAR(255) NOT NULL, covername VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_10DBBEC410FB0D18 ON manual (set_id)');
        $this->addSql('CREATE TABLE "set" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, number INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E61425DC96901F54 ON "set" (number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E61425DC5E237E06 ON "set" (name)');
        $this->addSql('CREATE INDEX set_number ON "set" (number)');
        $this->addSql('CREATE INDEX set_name ON "set" (name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE manual');
        $this->addSql('DROP TABLE "set"');
    }
}
