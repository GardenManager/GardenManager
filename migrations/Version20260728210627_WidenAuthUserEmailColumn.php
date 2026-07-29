<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260728210627_WidenAuthUserEmailColumn extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Widen auth_user.email from 180 to 255 to match the EmailAddress embeddable';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('auth_user')->modifyColumn('email', ['length' => 255]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('auth_user')->modifyColumn('email', ['length' => 180]);
    }
}
