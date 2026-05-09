<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303070033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bracket DROP FOREIGN KEY `FK_410E266EF7CC4348`');
        $this->addSql('DROP INDEX IDX_410E266EF7CC4348 ON bracket');
        $this->addSql('ALTER TABLE bracket MODIFY idBracket INT NOT NULL');
        $this->addSql('ALTER TABLE bracket ADD evenement_id INT NOT NULL, DROP idEvenement, CHANGE idBracket id_bracket INT AUTO_INCREMENT NOT NULL, CHANGE typeBracket type_bracket VARCHAR(50) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_bracket)');
        $this->addSql('ALTER TABLE bracket ADD CONSTRAINT FK_410E266EFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (idEvenement)');
        $this->addSql('CREATE INDEX IDX_410E266EFD02F13 ON bracket (evenement_id)');
        $this->addSql('ALTER TABLE chat_ai CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY `FK_67F068BC4B89032C`');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donation CHANGE amount amount NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY `FK_B26681EB03A8386`');
        $this->addSql('ALTER TABLE evenement CHANGE created_at created_at DATETIME NOT NULL, CHANGE created_by_id created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `match` DROP FOREIGN KEY `FK_7A5BC50551B74D9C`');
        $this->addSql('DROP INDEX IDX_7A5BC50551B74D9C ON `match`');
        $this->addSql('ALTER TABLE `match` CHANGE idBracket bracket_id INT NOT NULL');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT FK_7A5BC5056E8D78 FOREIGN KEY (bracket_id) REFERENCES bracket (idBracket) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_7A5BC5056E8D78 ON `match` (bracket_id)');
        $this->addSql('ALTER TABLE `match` RENAME INDEX idx_7a5bc505295a3090 TO IDX_7A5BC50589689FAE');
        $this->addSql('ALTER TABLE `match` RENAME INDEX idx_7a5bc5053bef9f7e TO IDX_7A5BC5059BDD3040');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY `FK_9275AE57A76ED395`');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT FK_9275AE57A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bracket DROP FOREIGN KEY FK_410E266EFD02F13');
        $this->addSql('DROP INDEX IDX_410E266EFD02F13 ON bracket');
        $this->addSql('ALTER TABLE bracket MODIFY id_bracket INT NOT NULL');
        $this->addSql('ALTER TABLE bracket ADD idEvenement INT DEFAULT NULL, DROP evenement_id, CHANGE id_bracket idBracket INT AUTO_INCREMENT NOT NULL, CHANGE type_bracket typeBracket VARCHAR(50) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (idBracket)');
        $this->addSql('ALTER TABLE bracket ADD CONSTRAINT `FK_410E266EF7CC4348` FOREIGN KEY (idEvenement) REFERENCES evenement (idEvenement) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_410E266EF7CC4348 ON bracket (idEvenement)');
        $this->addSql('ALTER TABLE chat_ai CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC4B89032C');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT `FK_67F068BC4B89032C` FOREIGN KEY (post_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE donation CHANGE amount amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EB03A8386');
        $this->addSql('ALTER TABLE evenement CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE created_by_id created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT `FK_B26681EB03A8386` FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `match` DROP FOREIGN KEY FK_7A5BC5056E8D78');
        $this->addSql('DROP INDEX IDX_7A5BC5056E8D78 ON `match`');
        $this->addSql('ALTER TABLE `match` CHANGE bracket_id idBracket INT NOT NULL');
        $this->addSql('ALTER TABLE `match` ADD CONSTRAINT `FK_7A5BC50551B74D9C` FOREIGN KEY (idBracket) REFERENCES bracket (idBracket) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_7A5BC50551B74D9C ON `match` (idBracket)');
        $this->addSql('ALTER TABLE `match` RENAME INDEX idx_7a5bc5059bdd3040 TO IDX_7A5BC5053BEF9F7E');
        $this->addSql('ALTER TABLE `match` RENAME INDEX idx_7a5bc50589689fae TO IDX_7A5BC505295A3090');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_abonnement DROP FOREIGN KEY FK_9275AE57A76ED395');
        $this->addSql('ALTER TABLE user_abonnement ADD CONSTRAINT `FK_9275AE57A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
