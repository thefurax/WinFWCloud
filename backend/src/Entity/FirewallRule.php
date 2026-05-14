<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FirewallRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $action = 'allow'; // allow, deny

    #[ORM\Column(length: 10)]
    private ?string $direction = 'inbound'; // inbound, outbound

    #[ORM\Column(length: 10)]
    private ?string $protocol = 'tcp';

    #[ORM\Column(nullable: true)]
    private ?int $dstPort = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $srcIp = '0.0.0.0/0';

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending';

    // Getters and setters...
}
