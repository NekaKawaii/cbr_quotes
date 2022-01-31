<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Event stream table for currency pair
 */
final class Version20220131110232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Event stream table for currency pair';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE currency_pair_stream (
                base VARCHAR(3),
                quote VARCHAR(3),
                event_class VARCHAR(255) NOT NULL,
                payload JSONB NOT NULL,
                occurred_at TIMESTAMP(6) NOT NULL,
                PRIMARY KEY (base, quote, occurred_at)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currency_pair_stream');
    }
}
