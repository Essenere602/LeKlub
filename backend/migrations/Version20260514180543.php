<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260514180543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create feed posts, comments and post reactions.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE feed_comment (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, post_id INT NOT NULL, author_id INT NOT NULL, deleted_by_id INT DEFAULT NULL, INDEX IDX_9474526C4B89032C (post_id), INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526CC76F1F52 (deleted_by_id), INDEX idx_comment_post_deleted (post_id, deleted_at), INDEX idx_comment_created_at (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, author_id INT NOT NULL, deleted_by_id INT DEFAULT NULL, INDEX IDX_5A8A6C8DF675F31B (author_id), INDEX IDX_5A8A6C8DC76F1F52 (deleted_by_id), INDEX idx_post_created_at (created_at), INDEX idx_post_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post_reaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1B3A8E564B89032C (post_id), INDEX IDX_1B3A8E56A76ED395 (user_id), INDEX idx_post_reaction_post_type (post_id, type), UNIQUE INDEX uniq_post_reaction_post_user (post_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE feed_comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feed_comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE feed_comment ADD CONSTRAINT FK_9474526CC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE post_reaction ADD CONSTRAINT FK_1B3A8E564B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_reaction ADD CONSTRAINT FK_1B3A8E56A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feed_comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE feed_comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE feed_comment DROP FOREIGN KEY FK_9474526CC76F1F52');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC76F1F52');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E564B89032C');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E56A76ED395');
        $this->addSql('DROP TABLE feed_comment');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_reaction');
    }
}
