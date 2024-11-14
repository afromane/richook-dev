<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241112215023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE potentiel (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, user_id INT DEFAULT NULL, code VARCHAR(25) DEFAULT NULL, name VARCHAR(255) NOT NULL, value BIGINT NOT NULL, periodicity VARCHAR(30) DEFAULT NULL, affectation VARCHAR(30) DEFAULT NULL, description LONGTEXT DEFAULT NULL, is_private TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, justificatif LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', img LONGTEXT NOT NULL, INDEX IDX_86A8B36412469DE2 (category_id), INDEX IDX_86A8B364A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE potentiel ADD CONSTRAINT FK_86A8B36412469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE potentiel ADD CONSTRAINT FK_86A8B364A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE potentiel DROP FOREIGN KEY FK_86A8B36412469DE2');
        $this->addSql('ALTER TABLE potentiel DROP FOREIGN KEY FK_86A8B364A76ED395');
        $this->addSql('DROP TABLE potentiel');
    }
}
