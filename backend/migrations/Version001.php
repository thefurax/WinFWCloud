<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for servers and firewall rules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE server (id UUID NOT NULL, hostname VARCHAR(255) NOT NULL, os_family VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, last_seen TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN server.last_seen IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE firewall_rule (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, action VARCHAR(10) NOT NULL, direction VARCHAR(10) NOT NULL, protocol VARCHAR(10) NOT NULL, dst_port INT DEFAULT NULL, src_ip VARCHAR(50) DEFAULT NULL, status VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE server');
        $this->addSql('DROP TABLE firewall_rule');
    }
}
