<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250911193849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, action_name VARCHAR(255) NOT NULL, action_count INT DEFAULT 0 NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', unlockable TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_FEF0481D5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badge_unlock (id INT AUTO_INCREMENT NOT NULL, badges_id INT NOT NULL, owner_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_585813AA538DA1D0 (badges_id), INDEX IDX_585813AA7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge_unlock ADD CONSTRAINT FK_585813AA538DA1D0 FOREIGN KEY (badges_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE badge_unlock ADD CONSTRAINT FK_585813AA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE badge_unlock DROP FOREIGN KEY FK_585813AA538DA1D0');
        $this->addSql('ALTER TABLE badge_unlock DROP FOREIGN KEY FK_585813AA7E3C61F9');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE badge_unlock');
    }
}
