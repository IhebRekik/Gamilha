<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223110244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `match` CHANGE dateMatch dateMatch DATETIME NOT NULL, CHANGE equipeA_id equipeA_id INT NOT NULL, CHANGE equipeB_id equipeB_id INT NOT NULL');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC505295A3090 FOREIGN KEY (equipeA_id) REFERENCES equipe (idEquipe)');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC5053BEF9F7E FOREIGN KEY (equipeB_id) REFERENCES equipe (idEquipe)');
        $this->addSql('ALTER TABLE stream ADD stream_key VARCHAR(255) NOT NULL, ADD is_live TINYINT NOT NULL, ADD api_video_id VARCHAR(255) DEFAULT NULL, ADD rtmp_server VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `match` DROP FOREIGN KEY FK_7A5BC505295A3090');
        $this->addSql('ALTER TABLE `match` DROP FOREIGN KEY FK_7A5BC5053BEF9F7E');
        $this->addSql('ALTER TABLE `match` CHANGE dateMatch dateMatch DATETIME DEFAULT NULL, CHANGE equipeA_id equipeA_id INT DEFAULT NULL, CHANGE equipeB_id equipeB_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stream DROP stream_key, DROP is_live, DROP api_video_id, DROP rtmp_server');
    }
}
