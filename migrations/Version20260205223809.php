<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205223809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnement (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_abonnement (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, user_id INT NOT NULL, abonnement_id INT NOT NULL, INDEX IDX_9275AE57A76ED395 (user_id), INDEX IDX_9275AE57F1D74413 (abonnement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT FK_9275AE57A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT FK_9275AE57F1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stream ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY FK_9275AE57A76ED395');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY FK_9275AE57F1D74413');
        $this->addSql('DROP TABLE abonnement');
        $this->addSql('DROP TABLE user_abonnement');
        $this->addSql('ALTER TABLE stream DROP image');
    }
}
