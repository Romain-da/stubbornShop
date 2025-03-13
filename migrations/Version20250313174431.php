<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313174431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
{
    // Vérifie si la colonne user existe avant de la supprimer
    $this->addSql('ALTER TABLE `order` ADD user_id INT NOT NULL, ADD items JSON NOT NULL, CHANGE status status VARCHAR(50) NOT NULL');
    $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON `order` (user_id)');
}

    public function down(Schema $schema): void
{
    // Suppression de la relation avec `user`
    $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
    $this->addSql('DROP INDEX IDX_F5299398A76ED395 ON `order`');
    
    // Remettre la colonne user sous forme de chaîne de caractères si nécessaire
    $this->addSql('ALTER TABLE `order` ADD user VARCHAR(255) NOT NULL, DROP user_id, DROP items, CHANGE status status VARCHAR(255) NOT NULL');
}
}
