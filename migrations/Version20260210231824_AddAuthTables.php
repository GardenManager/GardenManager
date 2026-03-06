<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20260210231824_AddAuthTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add authentication related tables (auth_user and auth_oidc)';
    }

    public function up(Schema $schema): void
    {
        $this->addAuthUserTable($schema);
        $this->addAuthOidcTable($schema);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('auth_oidc')->dropForeignKey('fk_auth_oidc_user_id_to_auth_user_id');
        $schema->dropTable('auth_oidc');
        $schema->dropTable('auth_user');
    }

    private function addAuthUserTable(Schema $schema): Table
    {
        $authUserTable = $schema->createTable('auth_user');

        $authUserTable->addColumn(
            'id',
            UlidType::NAME,
        );

        $authUserTable->addColumn(
            'email',
            Types::STRING,
            [
                'length' => 180,
            ],
        );

        $authUserTable->addColumn(
            'display_name',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $authUserTable->addColumn(
            'password',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 255,
            ],
        );

        $authUserTable->addColumn(
            'roles',
            Types::JSON,
        );

        $authUserTable->addColumn(
            'is_verified',
            Types::BOOLEAN,
        );

        $authUserTable->addColumn(
            'created_at',
            Types::DATETIME_IMMUTABLE,
        );

        $authUserTable->addColumn(
            'updated_at',
            Types::DATETIME_IMMUTABLE,
        );

        $authUserTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()->setColumnNames(
                    UnqualifiedName::unquoted('id')
                )
                    ->create()
            )
            ->addUniqueIndex(['email'], 'uq_email');

        return $authUserTable;
    }

    private function addAuthOidcTable(Schema $schema): Table
    {
        $authOidcTable = $schema->createTable('auth_oidc');

        $authOidcTable->addColumn(
            'id',
            UlidType::NAME,
        );

        $authOidcTable->addColumn(
            'provider',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $authOidcTable->addColumn(
            'subject',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $authOidcTable->addColumn(
            'linked_at',
            Types::DATETIME_IMMUTABLE,
        );

        $authOidcTable->addColumn(
            'user_id',
            UlidType::NAME
        );

        $authOidcTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()->setColumnNames(
                    UnqualifiedName::unquoted('id')
                )
                    ->create()
            )
            ->addIndex(['user_id'], 'idx_user_id')
            ->addUniqueIndex(['provider', 'subject'], 'uq_provider_subject');

        $authOidcTable->addForeignKeyConstraint(
            'auth_user',
            ['user_id'],
            ['id'],
            [
                'deferrable' => false,
                'onDelete' => 'CASCADE',
            ],
            'fk_auth_oidc_user_id_to_auth_user_id'
        );

        return $authOidcTable;
    }
}
