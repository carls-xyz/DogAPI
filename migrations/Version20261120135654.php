<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120135654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE breed (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, life_min INTEGER DEFAULT NULL, life_max INTEGER DEFAULT NULL, energy INTEGER DEFAULT NULL, barking INTEGER DEFAULT NULL, trainability INTEGER DEFAULT NULL, dog_api_id VARCHAR(36) NOT NULL, breed_group_id INTEGER NOT NULL, CONSTRAINT FK_F8AF884F1943F66D FOREIGN KEY (breed_group_id) REFERENCES breed_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F8AF884F1943F66D ON breed (breed_group_id)');
        $this->addSql('CREATE TABLE breed_temperament_trait (breed_id INTEGER NOT NULL, temperament_trait_id INTEGER NOT NULL, PRIMARY KEY (breed_id, temperament_trait_id), CONSTRAINT FK_68075F25A8B4A30F FOREIGN KEY (breed_id) REFERENCES breed (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68075F2587C7AB50 FOREIGN KEY (temperament_trait_id) REFERENCES temperament_trait (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_68075F25A8B4A30F ON breed_temperament_trait (breed_id)');
        $this->addSql('CREATE INDEX IDX_68075F2587C7AB50 ON breed_temperament_trait (temperament_trait_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE breed');
        $this->addSql('DROP TABLE breed_temperament_trait');
    }
}
