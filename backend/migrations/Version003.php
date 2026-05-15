<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Policy and update Server/FirewallRule relations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE policy (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE firewall_rule ADD policy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE firewall_rule ADD CONSTRAINT FK_POLICY FOREIGN KEY (policy_id) REFERENCES policy (id)');
        $this->addSql('ALTER TABLE server ADD policy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE server ADD CONSTRAINT FK_SERVER_POLICY FOREIGN KEY (policy_id) REFERENCES policy (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE server DROP CONSTRAINT FK_SERVER_POLICY');
        $this->addSql('ALTER TABLE server DROP policy_id');
        $this->addSql('ALTER TABLE firewall_rule DROP CONSTRAINT FK_POLICY');
        $this->addSql('ALTER TABLE firewall_rule DROP policy_id');
        $this->addSql('DROP TABLE policy');
    }
}
