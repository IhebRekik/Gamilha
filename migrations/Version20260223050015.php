<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223050015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_ai (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(10000) DEFAULT NULL, role VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, image_name VARCHAR(255) DEFAULT NULL, audio_name VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_ACF28790A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE chat_ai ADD CONSTRAINT FK_ACF28790A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_notification_post`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_notification_receiver`');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY `FK_notification_sender`');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, post_id INT DEFAULT NULL, type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, is_read TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX FK_notification_post (post_id), INDEX FK_notification_receiver (receiver_id), INDEX FK_notification_sender (sender_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_notification_post` FOREIGN KEY (post_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_notification_receiver` FOREIGN KEY (receiver_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT `FK_notification_sender` FOREIGN KEY (sender_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE chat_ai DROP FOREIGN KEY FK_ACF28790A76ED395');
        $this->addSql('DROP TABLE analysis');
        $this->addSql('DROP TABLE chat_ai');
        $this->addSql('ALTER TABLE stream ADD stream_key VARCHAR(255) NOT NULL, ADD is_live TINYINT NOT NULL, ADD api_video_id VARCHAR(255) DEFAULT NULL, ADD rtmp_server VARCHAR(255) DEFAULT NULL');
    }
}
