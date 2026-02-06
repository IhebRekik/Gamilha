<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204204255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_read TINYINT NOT NULL, sender_id INT DEFAULT NULL, recipient_id INT NOT NULL, INDEX IDX_FAB3FC16F624B39D (sender_id), INDEX IDX_FAB3FC16E92F8F78 (recipient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16E92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE abonnement ADD prix DOUBLE PRECISION NOT NULL, DROP date_debut, DROP date_fin');
        $this->addSql('ALTER TABLE user DROP abonnement_id');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY `FK_9275AE57A76ED395`');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY `FK_9275AE57F1D74413`');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT FK_9275AE57A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT FK_9275AE57F1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16F624B39D');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16E92F8F78');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('ALTER TABLE abonnement ADD date_debut DATETIME NOT NULL, ADD date_fin DATETIME NOT NULL, DROP prix');
        $this->addSql('ALTER TABLE user ADD abonnement_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY FK_9275AE57A76ED395');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY FK_9275AE57F1D74413');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT `FK_9275AE57A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT `FK_9275AE57F1D74413` FOREIGN KEY (abonnement_id) REFERENCES abonnement (id)');
    }
}
