<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223060530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_ai ADD updated_at DATETIME NOT NULL, CHANGE user_id user_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, post_id INT DEFAULT NULL, type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, is_read TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX FK_notification_post (post_id), INDEX FK_notification_receiver (receiver_id), INDEX FK_notification_sender (sender_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE analysis');
        $this->addSql('ALTER TABLE abonnement DROP options');
        $this->addSql('ALTER TABLE chat_ai DROP updated_at, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE stream ADD stream_key VARCHAR(255) NOT NULL, ADD is_live TINYINT NOT NULL, ADD api_video_id VARCHAR(255) DEFAULT NULL, ADD rtmp_server VARCHAR(255) DEFAULT NULL');
    }
}
