<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20260211013254_AddSellerTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add seller related tables (seller)';
    }

    public function up(Schema $schema): void
    {
        $this->createSellerTable($schema);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('seller');
    }

    private function createSellerTable(Schema $schema): Table
    {
        $sellerTable = $schema->createTable('seller');

        $sellerTable->addColumn(
            'id',
            UlidType::NAME
        );

        $sellerTable->addColumn(
            'name',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $sellerTable->addColumn(
            'description',
            Types::TEXT,
            [
                'notnull' => false,
                'default' => null,
            ],
        );

        $sellerTable->addColumn(
            'created_at',
            Types::DATETIME_IMMUTABLE,
        );

        $sellerTable->addColumn(
            'updated_at',
            Types::DATETIME_IMMUTABLE,
        );

        $sellerTable->addColumn(
            'deleted_at',
            Types::DATETIME_IMMUTABLE,
            [
                'notnull' => false,
                'default' => null,
            ],
        );

        $sellerTable->addColumn(
            'email',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $sellerTable->addColumn(
            'phone',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 50,
            ],
        );

        $sellerTable->addColumn(
            'address_street',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 255,
            ],
        );

        $sellerTable->addColumn(
            'address_city',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 255,
            ],
        );

        $sellerTable->addColumn(
            'address_postal_code',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 20,
            ],
        );

        $sellerTable->addColumn(
            'address_country',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 2,
            ],
        );

        $sellerTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()->setColumnNames(
                    UnqualifiedName::unquoted('id')
                )
                    ->create()
            );

        return $sellerTable;
    }
}
