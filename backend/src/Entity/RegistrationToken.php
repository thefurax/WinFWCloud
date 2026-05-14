<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RegistrationToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    private bool $used = false;

    public function getId(): ?int { return $this->id; }
    public function getToken(): ?string { return $this->token; }
    public function setToken(string $token): self { $this->token = $token; return $this; }
    public function getExpiresAt(): ?\DateTimeImmutable { return $this->expiresAt; }
    public function setExpiresAt(\DateTimeImmutable $expiresAt): self { $this->expiresAt = $expiresAt; return $this; }
    public function isUsed(): bool { return $this->used; }
    public function setUsed(bool $used): self { $this->used = $used; return $this; }
}
