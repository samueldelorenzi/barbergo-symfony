<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611002358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, id_client INT NOT NULL, id_barber INT NOT NULL, id_service INT NOT NULL, appointment_datetime DATETIME NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_FE38F844E173B1B8 (id_client), INDEX IDX_FE38F8445A7F1C49 (id_barber), INDEX IDX_FE38F8443F0033A2 (id_service), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE barber_barbershop (id INT AUTO_INCREMENT NOT NULL, id_barber INT NOT NULL, id_barbershop INT NOT NULL, INDEX IDX_30CE616C5A7F1C49 (id_barber), INDEX IDX_30CE616C7C53DCD2 (id_barbershop), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE barber_service (id INT AUTO_INCREMENT NOT NULL, id_barber INT NOT NULL, id_service INT NOT NULL, INDEX IDX_B6C881AB5A7F1C49 (id_barber), INDEX IDX_B6C881AB3F0033A2 (id_service), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE barbershop (id INT AUTO_INCREMENT NOT NULL, created_by INT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D9D95C05DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, id_barber INT NOT NULL, week_day VARCHAR(255) NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, INDEX IDX_5A3811FB5A7F1C49 (id_barber), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, id_barbershop INT NOT NULL, name VARCHAR(255) NOT NULL, duration_minutes INT NOT NULL, price NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E19D9AD27C53DCD2 (id_barbershop), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844E173B1B8 FOREIGN KEY (id_client) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8445A7F1C49 FOREIGN KEY (id_barber) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8443F0033A2 FOREIGN KEY (id_service) REFERENCES service (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_barbershop ADD CONSTRAINT FK_30CE616C5A7F1C49 FOREIGN KEY (id_barber) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_barbershop ADD CONSTRAINT FK_30CE616C7C53DCD2 FOREIGN KEY (id_barbershop) REFERENCES barbershop (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_service ADD CONSTRAINT FK_B6C881AB5A7F1C49 FOREIGN KEY (id_barber) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_service ADD CONSTRAINT FK_B6C881AB3F0033A2 FOREIGN KEY (id_service) REFERENCES service (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barbershop ADD CONSTRAINT FK_D9D95C05DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB5A7F1C49 FOREIGN KEY (id_barber) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD27C53DCD2 FOREIGN KEY (id_barbershop) REFERENCES barbershop (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844E173B1B8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8445A7F1C49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8443F0033A2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_barbershop DROP FOREIGN KEY FK_30CE616C5A7F1C49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_barbershop DROP FOREIGN KEY FK_30CE616C7C53DCD2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_service DROP FOREIGN KEY FK_B6C881AB5A7F1C49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barber_service DROP FOREIGN KEY FK_B6C881AB3F0033A2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE barbershop DROP FOREIGN KEY FK_D9D95C05DE12AB56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB5A7F1C49
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD27C53DCD2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE appointment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE barber_barbershop
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE barber_service
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE barbershop
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE schedule
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE service
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
