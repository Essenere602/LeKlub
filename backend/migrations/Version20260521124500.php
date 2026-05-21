<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add system notifications.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE system_notification (id INT AUTO_INCREMENT NOT NULL, recipient_id INT NOT NULL, type VARCHAR(40) NOT NULL, title VARCHAR(120) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_system_notification_recipient (recipient_id), INDEX idx_system_notification_read_at (read_at), INDEX idx_system_notification_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE system_notification ADD CONSTRAINT FK_6B7F4F4EE92F8F78 FOREIGN KEY (recipient_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE system_notification DROP FOREIGN KEY FK_6B7F4F4EE92F8F78');
        $this->addSql('DROP TABLE system_notification');
    }
}
