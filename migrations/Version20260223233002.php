<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223233002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipe_user (equipe_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_84DA47B76D861B89 (equipe_id), INDEX IDX_84DA47B7A76ED395 (user_id), PRIMARY KEY (equipe_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE evenement_equipe (idEvenement INT NOT NULL, idEquipe INT NOT NULL, INDEX IDX_97BC6A97F7CC4348 (idEvenement), INDEX IDX_97BC6A974758128F (idEquipe), PRIMARY KEY (idEvenement, idEquipe)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B76D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (idEquipe)');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evenement_equipe ADD CONSTRAINT FK_97BC6A97F7CC4348 FOREIGN KEY (idEvenement) REFERENCES evenement (idEvenement) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_equipe ADD CONSTRAINT FK_97BC6A974758128F FOREIGN KEY (idEquipe) REFERENCES equipe (idEquipe) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_NOTIFICATION_POST`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_NOTIFICATION_RECEIVER`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_NOTIFICATION_SENDER`');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE equipe ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA157E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2449BA157E3C61F9 ON equipe (owner_id)');
        $this->addSql('ALTER TABLE evenement ADD created_by_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_B26681EB03A8386 ON evenement (created_by_id)');
        $this->addSql('ALTER TABLE historique_paiement DROP FOREIGN KEY `FK_HISTORIQUE_ABONNEMENT`');
        $this->addSql('ALTER TABLE historique_paiement DROP FOREIGN KEY `FK_HISTORIQUE_USER`');
        $this->addSql('ALTER TABLE historique_paiement ADD CONSTRAINT FK_710402ECA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE historique_paiement ADD CONSTRAINT FK_710402ECF1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id)');
        $this->addSql('ALTER TABLE historique_paiement RENAME INDEX idx_historique_user TO IDX_710402ECA76ED395');
        $this->addSql('ALTER TABLE historique_paiement RENAME INDEX idx_historique_abonnement TO IDX_710402ECF1D74413');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC505295A3090 FOREIGN KEY (equipeA_id) REFERENCES equipe (idEquipe) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC5053BEF9F7E FOREIGN KEY (equipeB_id) REFERENCES equipe (idEquipe) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE post ADD likes INT NOT NULL');
        $this->addSql('ALTER TABLE stream DROP stream_key, DROP is_live, DROP api_video_id, DROP rtmp_server, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, post_id INT DEFAULT NULL, type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_read TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX FK_NOTIFICATION_SENDER (sender_id), INDEX FK_NOTIFICATION_RECEIVER (receiver_id), INDEX FK_NOTIFICATION_POST (post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_NOTIFICATION_POST` FOREIGN KEY (post_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_NOTIFICATION_RECEIVER` FOREIGN KEY (receiver_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_NOTIFICATION_SENDER` FOREIGN KEY (sender_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B76D861B89');
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B7A76ED395');
        $this->addSql('ALTER TABLE evenement_equipe DROP FOREIGN KEY FK_97BC6A97F7CC4348');
        $this->addSql('ALTER TABLE evenement_equipe DROP FOREIGN KEY FK_97BC6A974758128F');
        $this->addSql('DROP TABLE equipe_user');
        $this->addSql('DROP TABLE evenement_equipe');
        $this->addSql('ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA157E3C61F9');
        $this->addSql('DROP INDEX IDX_2449BA157E3C61F9 ON equipe');
        $this->addSql('ALTER TABLE equipe DROP owner_id');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EB03A8386');
        $this->addSql('DROP INDEX IDX_B26681EB03A8386 ON evenement');
        $this->addSql('ALTER TABLE evenement DROP created_by_id, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE historique_paiement DROP FOREIGN KEY FK_710402ECA76ED395');
        $this->addSql('ALTER TABLE historique_paiement DROP FOREIGN KEY FK_710402ECF1D74413');
        $this->addSql('ALTER TABLE historique_paiement ADD CONSTRAINT `FK_HISTORIQUE_ABONNEMENT` FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE historique_paiement ADD CONSTRAINT `FK_HISTORIQUE_USER` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE historique_paiement RENAME INDEX idx_710402eca76ed395 TO IDX_HISTORIQUE_USER');
        $this->addSql('ALTER TABLE historique_paiement RENAME INDEX idx_710402ecf1d74413 TO IDX_HISTORIQUE_ABONNEMENT');
        $this->addSql('ALTER TABLE `match` DROP FOREIGN KEY FK_7A5BC505295A3090');
        $this->addSql('ALTER TABLE `match` DROP FOREIGN KEY FK_7A5BC5053BEF9F7E');
        $this->addSql('ALTER TABLE post DROP likes');
        $this->addSql('ALTER TABLE stream ADD stream_key VARCHAR(255) DEFAULT \'\' NOT NULL, ADD is_live TINYINT DEFAULT 0 NOT NULL, ADD api_video_id VARCHAR(255) DEFAULT NULL, ADD rtmp_server VARCHAR(255) DEFAULT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }
}
