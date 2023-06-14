<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230613202029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create user_mod table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE user_mod (
                user_id VARCHAR(255) NOT NULL,
                mod_id VARCHAR(255) NOT NULL,
                PRIMARY KEY(user_id, mod_id),
                CONSTRAINT FK_user FOREIGN KEY (user_id) REFERENCES users(id),
                CONSTRAINT FK_mod FOREIGN KEY (user_id) REFERENCES users(id)
           ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
       ');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_mod');

    }
}
