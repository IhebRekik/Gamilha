<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224032340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement CHANGE avantages avantages JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE coaching_video CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE equipe CHANGE tag tag VARCHAR(10) DEFAULT NULL, CHANGE logo logo VARCHAR(255) DEFAULT NULL, CHANGE pays pays VARCHAR(50) DEFAULT NULL, CHANGE dateCreation dateCreation DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event CHANGE game game VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `match` CHANGE dateMatch dateMatch DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE mediaurl mediaurl VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE stream CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE thumbnail thumbnail VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE profile_image profile_image VARCHAR(255) DEFAULT NULL, CHANGE ban_until ban_until DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnement CHANGE avantages avantages LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE coaching_video CHANGE url url VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE equipe CHANGE tag tag VARCHAR(10) DEFAULT \'NULL\', CHANGE logo logo VARCHAR(255) DEFAULT \'NULL\', CHANGE pays pays VARCHAR(50) DEFAULT \'NULL\', CHANGE dateCreation dateCreation DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE evenement CHANGE image image VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE event CHANGE game game VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `match` CHANGE dateMatch dateMatch DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE password_reset_token MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE password_reset_token CHANGE id id INT NOT NULL, DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE post CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE mediaurl mediaurl VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE stream CHANGE thumbnail thumbnail VARCHAR(255) DEFAULT \'NULL\', CHANGE url url VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE profile_image profile_image VARCHAR(255) DEFAULT \'NULL\', CHANGE ban_until ban_until DATETIME DEFAULT \'NULL\'');
    }
}
