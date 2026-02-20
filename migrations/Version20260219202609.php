<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219202609 extends AbstractMigration
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
        $this->addSql('CREATE TABLE plant (id UUID NOT NULL, tenant_id UUID NOT NULL, local_name VARCHAR(255) NOT NULL, is_hybrid BOOLEAN NOT NULL, lifecycle VARCHAR(16) NOT NULL, genus VARCHAR(64) DEFAULT NULL, epithet VARCHAR(64) DEFAULT NULL, cultivar VARCHAR(64) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE seller (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, tenant_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT NULL, address_street VARCHAR(255) DEFAULT NULL, address_city VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(20) DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE tenant (id UUID NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE tenant_membership (id UUID NOT NULL, user_id UUID NOT NULL, role VARCHAR(16) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, tenant_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7EBE842D9033212A ON tenant_membership (tenant_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_tenant_user ON tenant_membership (tenant_id, user_id)');
        $this->addSql('ALTER TABLE auth_oidc ADD CONSTRAINT FK_FE0BB613A76ED395 FOREIGN KEY (user_id) REFERENCES auth_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE tenant_membership ADD CONSTRAINT FK_7EBE842D9033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_oidc DROP CONSTRAINT FK_FE0BB613A76ED395');
        $this->addSql('ALTER TABLE tenant_membership DROP CONSTRAINT FK_7EBE842D9033212A');
        $this->addSql('DROP TABLE auth_oidc');
        $this->addSql('DROP TABLE auth_user');
        $this->addSql('DROP TABLE plant');
        $this->addSql('DROP TABLE seller');
        $this->addSql('DROP TABLE tenant');
        $this->addSql('DROP TABLE tenant_membership');
    }
}
