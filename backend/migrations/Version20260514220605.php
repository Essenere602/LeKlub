<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260514220605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronize Doctrine index names for feed comments.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feed_comment RENAME INDEX idx_9474526c4b89032c TO IDX_2E6646194B89032C');
        $this->addSql('ALTER TABLE feed_comment RENAME INDEX idx_9474526cf675f31b TO IDX_2E664619F675F31B');
        $this->addSql('ALTER TABLE feed_comment RENAME INDEX idx_9474526cc76f1f52 TO IDX_2E664619C76F1F52');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feed_comment RENAME INDEX idx_2e664619f675f31b TO IDX_9474526CF675F31B');
        $this->addSql('ALTER TABLE feed_comment RENAME INDEX idx_2e664619c76f1f52 TO IDX_9474526CC76F1F52');
        $this->addSql('ALTER TABLE feed_comment RENAME INDEX idx_2e6646194b89032c TO IDX_9474526C4B89032C');
    }
}
