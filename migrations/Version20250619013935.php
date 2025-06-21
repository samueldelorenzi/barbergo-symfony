<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619013935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE join_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, barbershop_id INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E932E4FFA76ED395 (user_id), INDEX IDX_E932E4FF898B7F2A (barbershop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FF898B7F2A FOREIGN KEY (barbershop_id) REFERENCES barbershop (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE join_request DROP FOREIGN KEY FK_E932E4FFA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE join_request DROP FOREIGN KEY FK_E932E4FF898B7F2A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE join_request
        SQL);
    }
}
