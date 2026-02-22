<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260214120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Evenement: createdBy + equipesParticipantes; GameMatch: equipeA/equipeB nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_Evenement_created_by FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_Evenement_created_by ON evenement (created_by_id)');

        $this->addSql('CREATE TABLE evenement_equipe (idEvenement INT NOT NULL, idEquipe INT NOT NULL, INDEX IDX_EE_evenement (idEvenement), INDEX IDX_EE_equipe (idEquipe), PRIMARY KEY(idEvenement, idEquipe)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement_equipe ADD CONSTRAINT FK_EE_evenement FOREIGN KEY (idEvenement) REFERENCES evenement (idEvenement) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_equipe ADD CONSTRAINT FK_EE_equipe FOREIGN KEY (idEquipe) REFERENCES equipe (idEquipe) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE `match` MODIFY equipeA_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `match` MODIFY equipeB_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `match` MODIFY dateMatch DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_Evenement_created_by');
        $this->addSql('DROP INDEX IDX_Evenement_created_by ON evenement');
        $this->addSql('ALTER TABLE evenement DROP created_by_id');

        $this->addSql('DROP TABLE evenement_equipe');

        $this->addSql('ALTER TABLE `match` MODIFY equipeA_id INT NOT NULL');
        $this->addSql('ALTER TABLE `match` MODIFY equipeB_id INT NOT NULL');
        $this->addSql('ALTER TABLE `match` MODIFY dateMatch DATETIME NOT NULL');
    }
}
