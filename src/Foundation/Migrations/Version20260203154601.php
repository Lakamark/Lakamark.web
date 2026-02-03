<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203154601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content (id BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, access_level VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, published_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, author_id INT NOT NULL, kind VARCHAR(32) NOT NULL, INDEX IDX_FEC530A9F675F31B (author_id), INDEX idx_content_status (status), INDEX idx_content_access_level (access_level), INDEX idx_content_created_at (created_at), INDEX idx_content_feed (status, access_level, created_at), UNIQUE INDEX uniq_content_slug (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9F675F31B');
        $this->addSql('DROP TABLE content');
    }
}
