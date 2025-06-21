<?php

// src/Entity/JoinRequest.php
namespace App\Entity;

use App\Repository\JoinRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JoinRequestRepository::class)]
class JoinRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'joinRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'joinRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Barbershop $barbershop = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'pending'; // valores: pending, approved, rejected

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function setBarbershop(Barbershop $barbershop): static
    {
        $this->barbershop = $barbershop;
        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function getBarbershop(): ?Barbershop
    {
        return $this->barbershop;
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getStatus(): ?string
    {
        return $this->status;
    }
    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}
