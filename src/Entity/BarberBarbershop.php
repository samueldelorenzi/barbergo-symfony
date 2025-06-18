<?php

namespace App\Entity;

use App\Repository\BarberBarbershopRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BarberBarbershopRepository::class)]
class BarberBarbershop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_barber', referencedColumnName: 'id', nullable: false)]
    private ?User $id_barber = null;

    #[ORM\ManyToOne(targetEntity: Barbershop::class)]
    #[ORM\JoinColumn(name: 'id_barbershop', referencedColumnName: 'id', nullable: false)]
    private ?Barbershop $id_barbershop = null;

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

    public function getIdBarbershop(): ?Barbershop
    {
        return $this->id_barbershop;
    }

    public function setIdBarbershop(Barbershop $id_barbershop): static
    {
        $this->id_barbershop = $id_barbershop;
        return $this;
    }
}
