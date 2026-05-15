<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ip_version to firewall_rule';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE firewall_rule ADD ip_version VARCHAR(10) DEFAULT \'ipv4\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE firewall_rule DROP ip_version');
    }
}
