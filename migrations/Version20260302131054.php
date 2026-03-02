<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302131054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `fk_notification_receiver`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `fk_notification_sender`');
        $this->addSql('ALTER TABLE notification RENAME INDEX fk_notification_receiver TO IDX_BF5476CACD53EDB6');
        $this->addSql('ALTER TABLE notification RENAME INDEX fk_notification_sender TO IDX_BF5476CAF624B39D');
        $this->addSql('ALTER TABLE notification RENAME INDEX fk_bf5476ca4b89032c TO IDX_BF5476CA4B89032C');
        $this->addSql('ALTER TABLE social_media ADD likes INT NOT NULL');
        $this->addSql('ALTER TABLE stream CHANGE stream_key stream_key VARCHAR(255) NOT NULL, CHANGE is_live is_live TINYINT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE reports reports INT NOT NULL, CHANGE is_active is_active TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `fk_notification_receiver` FOREIGN KEY (receiver_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `fk_notification_sender` FOREIGN KEY (sender_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_bf5476caf624b39d TO fk_notification_sender');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_bf5476ca4b89032c TO FK_BF5476CA4B89032C');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_bf5476cacd53edb6 TO fk_notification_receiver');
        $this->addSql('ALTER TABLE social_media DROP likes');
        $this->addSql('ALTER TABLE stream CHANGE stream_key stream_key VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE is_live is_live TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE reports reports INT DEFAULT 0 NOT NULL, CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }
}
