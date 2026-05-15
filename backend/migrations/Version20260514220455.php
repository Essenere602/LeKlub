<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260514220455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create private messaging conversation and message tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, participant_one_id INT NOT NULL, participant_two_id INT NOT NULL, INDEX idx_conversation_participant_one (participant_one_id), INDEX idx_conversation_participant_two (participant_two_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, read_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307FF624B39D (sender_id), INDEX idx_message_conversation_created (conversation_id, created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E949E67092 FOREIGN KEY (participant_one_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E922BA975D FOREIGN KEY (participant_two_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E949E67092');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E922BA975D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE message');
    }
}
