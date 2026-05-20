<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add feed reports for posts and comments.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE feed_report (id INT AUTO_INCREMENT NOT NULL, reporter_id INT NOT NULL, post_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, resolved_by_id INT DEFAULT NULL, reason VARCHAR(40) NOT NULL, details LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', resolved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_feed_report_status (status), INDEX idx_feed_report_created_at (created_at), INDEX idx_feed_report_post (post_id), INDEX idx_feed_report_comment (comment_id), INDEX IDX_8F487C8FE1CFE6F5 (reporter_id), INDEX IDX_8F487C8F7988F1ED (resolved_by_id), UNIQUE INDEX uniq_feed_report_reporter_post (reporter_id, post_id), UNIQUE INDEX uniq_feed_report_reporter_comment (reporter_id, comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE feed_report ADD CONSTRAINT FK_8F487C8FE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feed_report ADD CONSTRAINT FK_8F487C8F4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feed_report ADD CONSTRAINT FK_8F487C8FF8697D13 FOREIGN KEY (comment_id) REFERENCES feed_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feed_report ADD CONSTRAINT FK_8F487C8F7988F1ED FOREIGN KEY (resolved_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feed_report DROP FOREIGN KEY FK_8F487C8FE1CFE6F5');
        $this->addSql('ALTER TABLE feed_report DROP FOREIGN KEY FK_8F487C8F4B89032C');
        $this->addSql('ALTER TABLE feed_report DROP FOREIGN KEY FK_8F487C8FF8697D13');
        $this->addSql('ALTER TABLE feed_report DROP FOREIGN KEY FK_8F487C8F7988F1ED');
        $this->addSql('DROP TABLE feed_report');
    }
}
