<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20260220223154_AddTenantTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tenant and permission system related tables (tenant, tenant_membership)';
    }

    public function up(Schema $schema): void
    {
        $this->createTenantTable($schema);
        $this->createTenantMembershipTable($schema);
        $this->write('Adding tenant_id to seller and plant tables');
        $this->addTenantIdToExistingComponents($schema);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('plant')->dropColumn('tenant_id');
        $schema->getTable('seller')->dropColumn('tenant_id');
        $schema->getTable('tenant_membership')->dropForeignKey('fk_tenant_membership_tenant_id_to_tenant_id');
        $schema->dropTable('tenant_membership');
        $schema->dropTable('tenant');
    }

    private function createTenantTable(Schema $schema): Table
    {
        $tenantTable = $schema->createTable('tenant');

        $tenantTable->addColumn(
            'id',
            UlidType::NAME
        );

        $tenantTable->addColumn(
            'name',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $tenantTable->addColumn(
            'created_at',
            Types::DATETIME_IMMUTABLE,
        );

        $tenantTable->addColumn(
            'updated_at',
            Types::DATETIME_IMMUTABLE,
        );

        $tenantTable->addColumn(
            'permissions_config',
            Types::JSONB,
        );

        $tenantTable->addColumn(
            'version',
            Types::INTEGER,
            [
                'default' => 1,
            ],
        );

        $tenantTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()->setColumnNames(
                    UnqualifiedName::unquoted('id')
                )
                    ->create()
            );

        return $tenantTable;
    }

    private function createTenantMembershipTable(Schema $schema): Table
    {
        $tenantMembershipTable = $schema->createTable('tenant_membership');

        $tenantMembershipTable->addColumn(
            'id',
            UlidType::NAME
        );

        $tenantMembershipTable->addColumn(
            'user_id',
            UlidType::NAME,
        );

        $tenantMembershipTable->addColumn(
            'is_owner',
            Types::BOOLEAN,
        );

        $tenantMembershipTable->addColumn(
            'created_at',
            Types::DATETIME_IMMUTABLE,
        );

        $tenantMembershipTable->addColumn(
            'tenant_id',
            UlidType::NAME
        );

        $tenantMembershipTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()->setColumnNames(
                    UnqualifiedName::unquoted('id')
                )
                    ->create()
            )
            ->addIndex(['tenant_id'], 'idx_tenant_id')
            ->addUniqueIndex(['tenant_id', 'user_id'], 'uq_tenant_id_user_id')
            ->addForeignKeyConstraint(
                'tenant',
                ['tenant_id'],
                ['id'],
                [
                    'deferrable' => false,
                    'onDelete' => 'CASCADE',
                ],
                'fk_tenant_membership_tenant_id_to_tenant_id'
            );

        return $tenantMembershipTable;
    }

    private function addTenantIdToExistingComponents(Schema $schema): void
    {
        $schema->getTable('seller')->addColumn(
            'tenant_id',
            UlidType::NAME
        );

        $schema->getTable('plant')->addColumn(
            'tenant_id',
            UlidType::NAME,
        );
    }
}
