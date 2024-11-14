<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114203522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, requester_id INT DEFAULT NULL, potentiel_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(50) DEFAULT NULL, INDEX IDX_659DF2AA7E3C61F9 (owner_id), INDEX IDX_659DF2AAED442CF4 (requester_id), INDEX IDX_659DF2AA3E7A70EB (potentiel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, chat_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, is_incomming TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8A8E26E91A9A7125 (chat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AAED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA3E7A70EB FOREIGN KEY (potentiel_id) REFERENCES potentiel (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E91A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat DROP FOREIGN KEY FK_659DF2AA7E3C61F9');
        $this->addSql('ALTER TABLE chat DROP FOREIGN KEY FK_659DF2AAED442CF4');
        $this->addSql('ALTER TABLE chat DROP FOREIGN KEY FK_659DF2AA3E7A70EB');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E91A9A7125');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE conversation');
    }
}
