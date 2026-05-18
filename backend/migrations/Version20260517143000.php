<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add per-user hidden private messages table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE message_hidden_for_user (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, user_id INT NOT NULL, hidden_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_message_hidden_message (message_id), INDEX idx_message_hidden_user (user_id), UNIQUE INDEX uniq_message_hidden_user (message_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message_hidden_for_user ADD CONSTRAINT FK_9B28D090537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message_hidden_for_user ADD CONSTRAINT FK_9B28D090A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message_hidden_for_user DROP FOREIGN KEY FK_9B28D090537A1329');
        $this->addSql('ALTER TABLE message_hidden_for_user DROP FOREIGN KEY FK_9B28D090A76ED395');
        $this->addSql('DROP TABLE message_hidden_for_user');
    }
}
