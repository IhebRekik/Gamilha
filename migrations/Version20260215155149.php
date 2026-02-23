<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215155149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, created_at DATETIME NOT NULL, post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_67F068BC4B89032C (post_id), INDEX IDX_67F068BCA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, post_id INT DEFAULT NULL, INDEX IDX_BF5476CAF624B39D (sender_id), INDEX IDX_BF5476CACD53EDB6 (receiver_id), INDEX IDX_BF5476CA4B89032C (post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, mediaurl VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_5A8A6C8DA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post_likes (post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DED1C2924B89032C (post_id), INDEX IDX_DED1C292A76ED395 (user_id), PRIMARY KEY (post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CACD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C2924B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_likes ADD CONSTRAINT FK_DED1C292A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `match` CHANGE dateMatch dateMatch DATETIME NOT NULL, CHANGE equipeA_id equipeA_id INT NOT NULL, CHANGE equipeB_id equipeB_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC4B89032C');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF624B39D');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CACD53EDB6');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA4B89032C');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C2924B89032C');
        $this->addSql('ALTER TABLE post_likes DROP FOREIGN KEY FK_DED1C292A76ED395');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_likes');
        $this->addSql('ALTER TABLE `match` CHANGE dateMatch dateMatch DATETIME DEFAULT NULL, CHANGE equipeA_id equipeA_id INT DEFAULT NULL, CHANGE equipeB_id equipeB_id INT DEFAULT NULL');
    }
}
