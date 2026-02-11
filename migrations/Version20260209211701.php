<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209211701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seller DROP CONSTRAINT fk_fb1ad3fc7e3c61f9');
        $this->addSql('DROP INDEX idx_fb1ad3fc7e3c61f9');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seller ADD CONSTRAINT fk_fb1ad3fc7e3c61f9 FOREIGN KEY (owner_id) REFERENCES auth_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_fb1ad3fc7e3c61f9 ON seller (owner_id)');
    }
}
