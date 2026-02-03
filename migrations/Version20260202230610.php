<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202230610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY `FK_9275AE57A76ED395`');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY `FK_9275AE57F1D74413`');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT FK_9275AE57A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT FK_9275AE57F1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY FK_9275AE57A76ED395');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY FK_9275AE57F1D74413');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT `FK_9275AE57A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT `FK_9275AE57F1D74413` FOREIGN KEY (abonnement_id) REFERENCES abonnement (id)');
    }
}
