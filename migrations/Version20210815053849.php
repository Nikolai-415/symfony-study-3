<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210815053849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE city_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE resume_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE vacancy_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE city (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE resume (id INT NOT NULL, city_to_work_in_id INT NOT NULL, desired_vacancy_id INT NOT NULL, full_name VARCHAR(255) NOT NULL, about TEXT DEFAULT NULL, work_experience INT NOT NULL, desired_salary DOUBLE PRECISION NOT NULL, birth_date DATE NOT NULL, sending_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, avatar BYTEA DEFAULT NULL, file BYTEA DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_60C1D0A062A6E579 ON resume (city_to_work_in_id)');
        $this->addSql('CREATE INDEX IDX_60C1D0A0B6AF4E7C ON resume (desired_vacancy_id)');
        $this->addSql('CREATE TABLE vacancy (id INT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A9346CBD727ACA70 ON vacancy (parent_id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A062A6E579 FOREIGN KEY (city_to_work_in_id) REFERENCES city (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0B6AF4E7C FOREIGN KEY (desired_vacancy_id) REFERENCES vacancy (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vacancy ADD CONSTRAINT FK_A9346CBD727ACA70 FOREIGN KEY (parent_id) REFERENCES vacancy (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE resume DROP CONSTRAINT FK_60C1D0A062A6E579');
        $this->addSql('ALTER TABLE resume DROP CONSTRAINT FK_60C1D0A0B6AF4E7C');
        $this->addSql('ALTER TABLE vacancy DROP CONSTRAINT FK_A9346CBD727ACA70');
        $this->addSql('DROP SEQUENCE city_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE resume_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE vacancy_id_seq CASCADE');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE resume');
        $this->addSql('DROP TABLE vacancy');
    }
}
