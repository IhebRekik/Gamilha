<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206164545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement DROP date_debut, DROP date_fin');
        $this->addSql('ALTER TABLE chat_message CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user DROP abonnement_id, CHANGE roles roles VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement ADD date_debut DATETIME NOT NULL, ADD date_fin DATETIME NOT NULL');
        $this->addSql('ALTER TABLE chat_message CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE user ADD abonnement_id INT NOT NULL, CHANGE roles roles JSON NOT NULL');
    }
}
