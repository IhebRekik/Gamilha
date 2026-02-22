<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222024819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_ai (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(10000) NOT NULL, role VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE analysis CHANGE result result VARCHAR(5000) DEFAULT NULL');
        $this->addSql('ALTER TABLE stream DROP stream_key, DROP is_live');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE chat_ai');
        $this->addSql('ALTER TABLE analysis CHANGE result result BLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE stream ADD stream_key VARCHAR(255) NOT NULL, ADD is_live TINYINT NOT NULL');
    }
}
