<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feed report decisions, user warnings and temporary suspension.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feed_report ADD decision VARCHAR(40) DEFAULT NULL, ADD admin_note LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD suspended_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_warning (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, report_id INT NOT NULL, created_by_id INT NOT NULL, content_type VARCHAR(40) NOT NULL, content_id INT NOT NULL, reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_user_warning_user (user_id), INDEX idx_user_warning_created_at (created_at), INDEX IDX_8F4C9D134BD2A4C0 (report_id), INDEX IDX_8F4C9D13B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_warning ADD CONSTRAINT FK_8F4C9D13A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_warning ADD CONSTRAINT FK_8F4C9D134BD2A4C0 FOREIGN KEY (report_id) REFERENCES feed_report (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_warning ADD CONSTRAINT FK_8F4C9D13B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_warning DROP FOREIGN KEY FK_8F4C9D13A76ED395');
        $this->addSql('ALTER TABLE user_warning DROP FOREIGN KEY FK_8F4C9D134BD2A4C0');
        $this->addSql('ALTER TABLE user_warning DROP FOREIGN KEY FK_8F4C9D13B03A8386');
        $this->addSql('DROP TABLE user_warning');
        $this->addSql('ALTER TABLE feed_report DROP decision, DROP admin_note');
        $this->addSql('ALTER TABLE `user` DROP suspended_until');
    }
}
