<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201228234430 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create alert table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('
            CREATE TABLE alerts (
                id INT AUTO_INCREMENT NOT NULL,
                user_id VARCHAR(255) DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                file VARCHAR(255) NOT NULL,
                active TINYINT(1) DEFAULT \'0\' NOT NULL,
                INDEX IDX_F77AC06BA76ED395 (user_id),
                UNIQUE INDEX user_name (user_id, name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE alerts
            ADD CONSTRAINT FK_F77AC06BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        ');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE alerts');
    }
}
