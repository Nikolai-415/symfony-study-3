<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820185717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE vacancies (id INT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX ukey_vacancies_name_and_parent_id ON vacancies (name, parent_id)');
        $this->addSql('CREATE INDEX IDX_99165A59727ACA70 ON vacancies (parent_id)');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE resumes (id INT NOT NULL, city_to_work_in_id INT DEFAULT 0, desired_vacancy_id INT DEFAULT 0, full_name VARCHAR(255) NOT NULL, about TEXT NOT NULL, work_experience INT DEFAULT 0, desired_salary DOUBLE PRECISION NOT NULL, birth_date DATE NOT NULL, sending_datetime TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'now()\', avatar VARCHAR(64) NOT NULL, file VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX ukey_resumes_full_name ON resumes (full_name)');
        $this->addSql('CREATE INDEX IDX_CDB8AD3362A6E579 ON resumes (city_to_work_in_id)');
        $this->addSql('CREATE INDEX IDX_CDB8AD33B6AF4E7C ON resumes (desired_vacancy_id)');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE users (id INT NOT NULL, login VARCHAR(32) NOT NULL, password_sha512 VARCHAR(128) NOT NULL, registration_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'now()\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX ukey_users_login ON users (login)');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE cities (id INT NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE vacancies');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE resumes');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE users');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE cities');
    }
}
