<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Server
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'GUID')]
    #[ORM\Column(type: 'guid')]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    private ?string $hostname = null;

    #[ORM\Column(length: 50)]
    private ?string $osFamily = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'offline';

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $lastSeen = null;

    #[ORM\ManyToOne(targetEntity: Policy::class)]
    private ?Policy $policy = null;

    public function getId(): ?string { return $this->id; }
    public function getHostname(): ?string { return $this->hostname; }
    public function setHostname(string $hostname): self { $this->hostname = $hostname; return $this; }
    public function getOsFamily(): ?string { return $this->osFamily; }
    public function setOsFamily(string $osFamily): self { $this->osFamily = $osFamily; return $this; }
    public function getPolicy(): ?Policy { return $this->policy; }
    public function setPolicy(?Policy $policy): self { $this->policy = $policy; return $this; }
}
