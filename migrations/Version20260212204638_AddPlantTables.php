<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20260212204638_AddPlantTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add plant related tables (plant)';
    }

    public function up(Schema $schema): void
    {
        $this->createPlantTable($schema);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('plant');
    }

    private function createPlantTable(Schema $schema): Table
    {
        $plantTable = $schema->createTable('plant');

        $plantTable->addColumn(
            'id',
            UlidType::NAME,
        );

        $plantTable->addColumn(
            'local_name',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $plantTable->addColumn(
            'is_hybrid',
            Types::BOOLEAN,
        );

        $plantTable->addColumn(
            'lifecycle',
            Types::STRING,
            [
                'length' => 16,
            ],
        );

        $plantTable->addColumn(
            'genus',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 64,
            ],
        );

        $plantTable->addColumn(
            'epithet',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 64,
            ],
        );

        $plantTable->addColumn(
            'cultivar',
            Types::STRING,
            [
                'notnull' => false,
                'default' => null,
                'length' => 64,
            ],
        );

        $plantTable->addColumn(
            'created_at',
            Types::DATETIME_IMMUTABLE,
        );

        $plantTable->addColumn(
            'updated_at',
            Types::DATETIME_IMMUTABLE,
            [
                'notnull' => false,
                'default' => null,
            ]
        );

        $plantTable->addColumn(
            'deleted_at',
            Types::DATETIME_IMMUTABLE,
            [
                'notnull' => false,
                'default' => null,
            ],
        );

        $plantTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()->setColumnNames(
                    UnqualifiedName::unquoted('id')
                )
                    ->create()
            );

        return $plantTable;
    }
}
