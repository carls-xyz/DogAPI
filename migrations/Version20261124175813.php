<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261124175813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__breed AS SELECT id, name, description, life_min, life_max, energy, barking, trainability, dog_api_id, breed_group_id FROM breed');
        $this->addSql('DROP TABLE breed');
        $this->addSql('CREATE TABLE breed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, life_min INTEGER DEFAULT NULL, life_max INTEGER DEFAULT NULL, energy INTEGER DEFAULT NULL, barking INTEGER DEFAULT NULL, trainability INTEGER DEFAULT NULL, dog_api_id VARCHAR(36) NOT NULL, breed_group_id INTEGER NOT NULL, CONSTRAINT FK_F8AF884F1943F66D FOREIGN KEY (breed_group_id) REFERENCES breed_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO breed (id, name, description, life_min, life_max, energy, barking, trainability, dog_api_id, breed_group_id) SELECT id, name, description, life_min, life_max, energy, barking, trainability, dog_api_id, breed_group_id FROM __temp__breed');
        $this->addSql('DROP TABLE __temp__breed');
        $this->addSql('CREATE INDEX IDX_F8AF884F1943F66D ON breed (breed_group_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_breed_dog_api_id ON breed (dog_api_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__breed_group AS SELECT id, name, dog_api_id FROM breed_group');
        $this->addSql('DROP TABLE breed_group');
        $this->addSql('CREATE TABLE breed_group (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, dog_api_id VARCHAR(36) NOT NULL)');
        $this->addSql('INSERT INTO breed_group (id, name, dog_api_id) SELECT id, name, dog_api_id FROM __temp__breed_group');
        $this->addSql('DROP TABLE __temp__breed_group');
        $this->addSql('CREATE UNIQUE INDEX uniq_breed_group_dog_api_id ON breed_group (dog_api_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__fact AS SELECT id, fact, dog_api_id FROM fact');
        $this->addSql('DROP TABLE fact');
        $this->addSql('CREATE TABLE fact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, fact CLOB NOT NULL, dog_api_id VARCHAR(36) NOT NULL)');
        $this->addSql('INSERT INTO fact (id, fact, dog_api_id) SELECT id, fact, dog_api_id FROM __temp__fact');
        $this->addSql('DROP TABLE __temp__fact');
        $this->addSql('CREATE UNIQUE INDEX uniq_fact_dog_api_id ON fact (dog_api_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__temperament_trait AS SELECT id, name FROM temperament_trait');
        $this->addSql('DROP TABLE temperament_trait');
        $this->addSql('CREATE TABLE temperament_trait (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL)');
        $this->addSql('INSERT INTO temperament_trait (id, name) SELECT id, name FROM __temp__temperament_trait');
        $this->addSql('DROP TABLE __temp__temperament_trait');
        $this->addSql('CREATE UNIQUE INDEX uniq_temperament_trait_name ON temperament_trait (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__breed AS SELECT id, name, description, life_min, life_max, energy, barking, trainability, dog_api_id, breed_group_id FROM breed');
        $this->addSql('DROP TABLE breed');
        $this->addSql('CREATE TABLE breed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, life_min INTEGER DEFAULT NULL, life_max INTEGER DEFAULT NULL, energy INTEGER DEFAULT NULL, barking INTEGER DEFAULT NULL, trainability INTEGER DEFAULT NULL, dog_api_id VARCHAR(36) NOT NULL, breed_group_id INTEGER NOT NULL, CONSTRAINT FK_F8AF884F1943F66D FOREIGN KEY (breed_group_id) REFERENCES breed_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO breed (id, name, description, life_min, life_max, energy, barking, trainability, dog_api_id, breed_group_id) SELECT id, name, description, life_min, life_max, energy, barking, trainability, dog_api_id, breed_group_id FROM __temp__breed');
        $this->addSql('DROP TABLE __temp__breed');
        $this->addSql('CREATE INDEX IDX_F8AF884F1943F66D ON breed (breed_group_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__breed_group AS SELECT id, name, dog_api_id FROM breed_group');
        $this->addSql('DROP TABLE breed_group');
        $this->addSql('CREATE TABLE breed_group (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, dog_api_id VARCHAR(36) NOT NULL)');
        $this->addSql('INSERT INTO breed_group (id, name, dog_api_id) SELECT id, name, dog_api_id FROM __temp__breed_group');
        $this->addSql('DROP TABLE __temp__breed_group');
        $this->addSql('CREATE TEMPORARY TABLE __temp__fact AS SELECT id, fact, dog_api_id FROM fact');
        $this->addSql('DROP TABLE fact');
        $this->addSql('CREATE TABLE fact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, fact CLOB NOT NULL, dog_api_id VARCHAR(36) NOT NULL)');
        $this->addSql('INSERT INTO fact (id, fact, dog_api_id) SELECT id, fact, dog_api_id FROM __temp__fact');
        $this->addSql('DROP TABLE __temp__fact');
        $this->addSql('CREATE TEMPORARY TABLE __temp__temperament_trait AS SELECT id, name FROM temperament_trait');
        $this->addSql('DROP TABLE temperament_trait');
        $this->addSql('CREATE TABLE temperament_trait (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL)');
        $this->addSql('INSERT INTO temperament_trait (id, name) SELECT id, name FROM __temp__temperament_trait');
        $this->addSql('DROP TABLE __temp__temperament_trait');
    }
}
