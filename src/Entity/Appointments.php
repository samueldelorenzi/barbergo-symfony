<?php

namespace App\Entity;

use App\Repository\AppointmentsRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[ORM\Entity(repositoryClass: AppointmentsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Appointments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id', nullable: false)]
    private ?Users $id_client = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_barber', referencedColumnName: 'id', nullable: false)]
    private ?Users $id_barber = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id', nullable: false)]
    private ?Service $id_service = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $appointment_datetime = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    // Getters e setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdClient(): ?Users
    {
        return $this->id_client;
    }

    public function setIdClient(Users $id_client): static
    {
        $this->id_client = $id_client;
        return $this;
    }

    public function getIdBarber(): ?Users
    {
        return $this->id_barber;
    }

    public function setIdBarber(Users $id_barber): static
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

    public function getAppointmentDatetime(): ?\DateTimeInterface
    {
        return $this->appointment_datetime;
    }

    public function setAppointmentDatetime(\DateTimeInterface $appointment_datetime): static
    {
        $this->appointment_datetime = $appointment_datetime;
        return $this;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }
}
