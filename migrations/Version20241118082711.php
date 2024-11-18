<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241118082711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE besoin ADD category_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD is_private TINYINT(1) NOT NULL, ADD img LONGTEXT NOT NULL, ADD periode VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE besoin ADD CONSTRAINT FK_8118E81112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE besoin ADD CONSTRAINT FK_8118E811A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8118E81112469DE2 ON besoin (category_id)');
        $this->addSql('CREATE INDEX IDX_8118E811A76ED395 ON besoin (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE besoin DROP FOREIGN KEY FK_8118E81112469DE2');
        $this->addSql('ALTER TABLE besoin DROP FOREIGN KEY FK_8118E811A76ED395');
        $this->addSql('DROP INDEX IDX_8118E81112469DE2 ON besoin');
        $this->addSql('DROP INDEX IDX_8118E811A76ED395 ON besoin');
        $this->addSql('ALTER TABLE besoin DROP category_id, DROP user_id, DROP description, DROP created_at, DROP is_private, DROP img, DROP periode');
    }
}
