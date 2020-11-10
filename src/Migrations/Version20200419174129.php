<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200419174129 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE compte (id INT AUTO_INCREMENT NOT NULL, nom_utilisateur VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, genre VARCHAR(6) NOT NULL, adresse VARCHAR(255) NOT NULL, province VARCHAR(3) NOT NULL, code_postal VARCHAR(8) NOT NULL, telephone VARCHAR(12) NOT NULL, mot_passe VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE catagorie');
        $this->addSql('DROP TABLE produits');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE catagorie (idCategorie INT AUTO_INCREMENT NOT NULL, nom VARCHAR(25) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, Marvel TINYINT(1) NOT NULL, UNIQUE INDEX nom (nom), PRIMARY KEY(idCategorie)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE produits (id INT AUTO_INCREMENT NOT NULL, nomProduit VARCHAR(50) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, idCategorie INT DEFAULT NULL, description VARCHAR(500) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, quatiteStock INT DEFAULT NULL, quantiteminimale INT DEFAULT NULL, prix INT NOT NULL, DC INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE compte');
    }
}
