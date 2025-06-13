<?php

namespace App\Entity;

use App\Repository\BarberServiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BarberServiceRepository::class)]
class BarberService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // Relacionamento com User (id_barber)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_barber', referencedColumnName: 'id', nullable: false)]
    private ?User $id_barber = null;

    // Relacionamento com Service (id_service)
    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id', nullable: false)]
    private ?Service $id_service = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdBarber(): ?User
    {
        return $this->id_barber;
    }

    public function setIdBarber(User $id_barber): static
    {
        $this->id_barber = $id_barber;
        return $this;
    }

    public function getIdService(): ?Service
    {
        return $this->id_service;
    }

    public function setIdService(Service $id_service): static
    {
        $this->id_service = $id_service;
        return $this;
    }
}
