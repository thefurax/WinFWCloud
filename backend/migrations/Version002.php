<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Registration tokens and UUID generation';
    }

    public function up(Schema $schema): void
    {
        // Fix Server table for auto-uuid
        $this->addSql('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');
        $this->addSql('ALTER TABLE server ALTER id SET DEFAULT gen_random_uuid()');
        $this->addSql('ALTER TABLE server ALTER last_seen SET DEFAULT CURRENT_TIMESTAMP');

        // Create RegistrationToken table
        $this->addSql('CREATE TABLE registration_token (id SERIAL NOT NULL, token VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, used BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D985B8805F37A13B ON registration_token (token)');
        $this->addSql('COMMENT ON COLUMN registration_token.expires_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE registration_token');
        $this->addSql('ALTER TABLE server ALTER id DROP DEFAULT');
    }
}
