<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209174005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth_oidc (id UUID NOT NULL, provider VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, linked_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_FE0BB613A76ED395 ON auth_oidc (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_provider_subject ON auth_oidc (provider, subject)');
        $this->addSql('CREATE TABLE auth_user (id UUID NOT NULL, email VARCHAR(180) NOT NULL, display_name VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, is_verified BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3B536FDE7927C74 ON auth_user (email)');
        $this->addSql('CREATE TABLE seller (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT NULL, address_street VARCHAR(255) DEFAULT NULL, address_city VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(20) DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, owner_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_FB1AD3FC7E3C61F9 ON seller (owner_id)');
        $this->addSql('ALTER TABLE auth_oidc ADD CONSTRAINT FK_FE0BB613A76ED395 FOREIGN KEY (user_id) REFERENCES auth_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE seller ADD CONSTRAINT FK_FB1AD3FC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES auth_user (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_oidc DROP CONSTRAINT FK_FE0BB613A76ED395');
        $this->addSql('ALTER TABLE seller DROP CONSTRAINT FK_FB1AD3FC7E3C61F9');
        $this->addSql('DROP TABLE auth_oidc');
        $this->addSql('DROP TABLE auth_user');
        $this->addSql('DROP TABLE seller');
    }
}
