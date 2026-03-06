<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20260304200714_AddCustomAttributeSupport extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tables for custom attribute support (custom_attribute_definition and custom_attribute_value)';
    }

    public function up(Schema $schema): void
    {
        $this->addAttributeDefinitionTable($schema);
        $this->addAttributeValueTable($schema);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('custom_attribute_value')->dropForeignKey('fk_attribute_value_definition_id_to_custom_attribute_definition_id');
        $schema->dropTable('custom_attribute_definition');
        $schema->dropTable('custom_attribute_value');
    }

    private function addAttributeDefinitionTable(Schema $schema): Table
    {
        $attributeDefinitionTable = $schema->createTable('custom_attribute_definition');

        $attributeDefinitionTable->addColumn(
            'id',
            UlidType::NAME,
        );

        $attributeDefinitionTable->addColumn(
            'tenant_id',
            UlidType::NAME,
        );

        $attributeDefinitionTable->addColumn(
            'entity_type',
            Types::STRING,
            [
                'length' => 64,
            ]
        );

        $attributeDefinitionTable->addColumn(
            'name',
            Types::STRING,
            [
                'length' => 128,
            ]
        );

        $attributeDefinitionTable->addColumn(
            'label',
            Types::STRING,
            [
                'length' => 255,
            ],
        );

        $attributeDefinitionTable->addColumn(
            'type',
            Types::STRING,
            [
                'length' => 16,
            ],
        );

        $attributeDefinitionTable->addColumn(
            'options',
            Types::JSON,
            [
                'notnull' => false,
                'default' => null,
            ],
        );

        $attributeDefinitionTable->addColumn(
            'required',
            Types::BOOLEAN,
        );

        $attributeDefinitionTable->addColumn(
            'sort_order',
            Types::INTEGER,
        );

        $attributeDefinitionTable->addColumn(
            'created_at',
            Types::DATETIME_IMMUTABLE,
        );

        $attributeDefinitionTable->addColumn(
            'updated_at',
            Types::DATETIME_IMMUTABLE,
        );

        $attributeDefinitionTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()->setColumnNames(
                    UnqualifiedName::unquoted('id')
                )
                    ->create()
            )
            ->addIndex(['tenant_id', 'entity_type'], 'idx_definition_tenant_entity')
            ->addUniqueIndex(['tenant_id', 'entity_type', 'name'], 'uq_definition_tenant_entity_name');

        return $attributeDefinitionTable;
    }

    private function addAttributeValueTable(Schema $schema): Table
    {
        $attributeValueTable = $schema->createTable('custom_attribute_value');

        $attributeValueTable->addColumn(
            'id',
            UlidType::NAME,
        );

        $attributeValueTable->addColumn(
            'entity_type',
            Types::STRING,
            [
                'length' => 64,
            ],
        );

        $attributeValueTable->addColumn(
            'entity_id',
            UlidType::NAME,
        );

        $attributeValueTable->addColumn(
            'string_value',
            Types::STRING,
            [
                'notnull' => false,
                'length' => 1024,
                'default' => null,
            ],
        );

        $attributeValueTable->addColumn(
            'integer_value',
            Types::INTEGER,
            [
                'notnull' => false,
                'default' => null,
            ],
        );

        $attributeValueTable->addColumn(
            'decimal_value',
            Types::DECIMAL,
            [
                'notnull' => false,
                'default' => null,
                'precision' => 15,
                'scale' => 4,
            ],
        );

        $attributeValueTable->addColumn(
            'date_value',
            Types::DATE_IMMUTABLE,
            [
                'notnull' => false,
                'default' => null,
            ],
        );

        $attributeValueTable->addColumn(
            'boolean_value',
            Types::BOOLEAN,
            [
                'notnull' => false,
                'default' => null,
            ],
        );

        $attributeValueTable->addColumn(
            'updated_at',
            Types::DATETIME_IMMUTABLE,
        );

        $attributeValueTable->addColumn(
            'definition_id',
            UlidType::NAME,
        );

        $attributeValueTable
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()
                    ->setColumnNames(
                        UnqualifiedName::unquoted('id')
                    )
                    ->create()
            )
            ->addIndex(['definition_id'], 'idx_definition_id')
            ->addIndex(['entity_type', 'entity_id'], 'idx_value_entity')
            ->addUniqueIndex(['entity_id', 'definition_id'], 'uq_value_entity_definition')
            ->addForeignKeyConstraint(
                'custom_attribute_definition',
                ['definition_id'],
                ['id'],
                [
                    'deferrable' => false,
                    'onDelete' => 'CASCADE',
                ],
                'fk_attribute_value_definition_id_to_custom_attribute_definition_id'
            );

        return $attributeValueTable;
    }
}
