<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230613202127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create user_mod table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE user_mod
            ADD alert_amount INT NOT NULL DEFAULT 20,
            ADD theme VARCHAR(255) DEFAULT NULL
       ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_mod');

    }
}
