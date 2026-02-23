<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222205246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, post_id INT DEFAULT NULL, INDEX IDX_BF5476CAF624B39D (sender_id), INDEX IDX_BF5476CACD53EDB6 (receiver_id), INDEX IDX_BF5476CA4B89032C (post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CACD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post DROP likes');
        $this->addSql('ALTER TABLE stream DROP stream_key, DROP is_live, DROP api_video_id, DROP rtmp_server');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF624B39D');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CACD53EDB6');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA4B89032C');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE post ADD likes INT NOT NULL');
        $this->addSql('ALTER TABLE stream ADD stream_key VARCHAR(255) NOT NULL, ADD is_live TINYINT NOT NULL, ADD api_video_id VARCHAR(255) DEFAULT NULL, ADD rtmp_server VARCHAR(255) DEFAULT NULL');
    }
}
