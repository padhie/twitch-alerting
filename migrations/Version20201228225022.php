<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201228225022 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create user table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE users (
                id VARCHAR(255) NOT NULL,
                twitch_uuid VARCHAR(255) NOT NULL,
                twitch_login VARCHAR(255) NOT NULL,
                UNIQUE INDEX twitchUuid (twitch_uuid),
                UNIQUE INDEX twitchLogin (twitch_login),
                PRIMARY KEY(id)
           ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
       ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
    }
}
