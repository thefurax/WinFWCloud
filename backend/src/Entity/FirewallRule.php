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
    private ?string $action = 'allow';

    #[ORM\Column(length: 10)]
    private ?string $direction = 'inbound';

    #[ORM\Column(length: 10)]
    private ?string $protocol = 'tcp';

    #[ORM\Column(nullable: true)]
    private ?int $dstPort = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $srcIp = '0.0.0.0/0';

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending';

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getAction(): ?string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }
    public function getDirection(): ?string { return $this->direction; }
    public function setDirection(string $direction): self { $this->direction = $direction; return $this; }
    public function getProtocol(): ?string { return $this->protocol; }
    public function setProtocol(string $protocol): self { $this->protocol = $protocol; return $this; }
    public function getDstPort(): ?int { return $this->dstPort; }
    public function setDstPort(?int $dstPort): self { $this->dstPort = $dstPort; return $this; }
    public function getSrcIp(): ?string { return $this->srcIp; }
    public function setSrcIp(?string $srcIp): self { $this->srcIp = $srcIp; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
}
