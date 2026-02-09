<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208115201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_detail ADD cadeau_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_detail ADD CONSTRAINT FK_B3EE405D9D5ED84 FOREIGN KEY (cadeau_id) REFERENCES cadeau (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B3EE405D9D5ED84 ON payment_detail (cadeau_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_detail DROP CONSTRAINT FK_B3EE405D9D5ED84');
        $this->addSql('DROP INDEX IDX_B3EE405D9D5ED84');
        $this->addSql('ALTER TABLE payment_detail DROP cadeau_id');
    }
}
