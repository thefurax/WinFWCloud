<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $protocol = 'tcp'; // tcp, udp, icmp, any

    #[ORM\Column(nullable: true)]
    private ?int $portStart = null;

    #[ORM\Column(nullable: true)]
    private ?int $portEnd = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getProtocol(): ?string { return $this->protocol; }
    public function setProtocol(string $protocol): self { $this->protocol = $protocol; return $this; }
    public function getPortStart(): ?int { return $this->portStart; }
    public function setPortStart(?int $portStart): self { $this->portStart = $portStart; return $this; }
    public function getPortEnd(): ?int { return $this->portEnd; }
    public function setPortEnd(?int $portEnd): self { $this->portEnd = $portEnd; return $this; }
}
