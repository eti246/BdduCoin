<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200503221630 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commande ADD comande_detail_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D79DD3F09 FOREIGN KEY (comande_detail_id) REFERENCES commande (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D79DD3F09 ON commande (comande_detail_id)');
        $this->addSql('ALTER TABLE commande_detail ADD produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commande_detail ADD CONSTRAINT FK_2C528446F347EFB FOREIGN KEY (produit_id) REFERENCES produits (id)');
        $this->addSql('CREATE INDEX IDX_2C528446F347EFB ON commande_detail (produit_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D79DD3F09');
        $this->addSql('DROP INDEX IDX_6EEAA67D79DD3F09 ON commande');
        $this->addSql('ALTER TABLE commande DROP comande_detail_id');
        $this->addSql('ALTER TABLE commande_detail DROP FOREIGN KEY FK_2C528446F347EFB');
        $this->addSql('DROP INDEX IDX_2C528446F347EFB ON commande_detail');
        $this->addSql('ALTER TABLE commande_detail DROP produit_id');
    }
}
