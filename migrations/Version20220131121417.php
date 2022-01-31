<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Projection table for read model of currency pair
 */
final class Version20220131121417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Projection table for read model of currency pair';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE currency_pair_projection (
                base VARCHAR(3),
                quote VARCHAR(3),
                date VARCHAR(20) NOT NULL,
                current NUMERIC(10, 5) NOT NULL,
                previous NUMERIC(10, 5) DEFAULT NULL,
                delta VARCHAR(20) DEFAULT NULL,
                last_updated_at VARCHAR(30) NOT NULL,
                PRIMARY KEY (base, quote)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currency_pair_projection');
    }
}
