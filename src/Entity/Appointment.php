<?php

namespace App\Entity;

use App\Repository\AppointmentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id', nullable: false)]
    private ?User $id_client = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_barber', referencedColumnName: 'id', nullable: false)]
    private ?User $id_barber = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id', nullable: false)]
    private ?Service $id_service = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $appointment_date = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $appointment_time = null;

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

    // Getters e Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdClient(): ?User
    {
        return $this->id_client;
    }

    public function setIdClient(User $id_client): static
    {
        $this->id_client = $id_client;
        return $this;
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

    public function getAppointmentDate(): ?\DateTimeInterface
    {
        return $this->appointment_date;
    }

    public function setAppointmentDate(\DateTimeInterface $appointment_date): static
    {
        $this->appointment_date = $appointment_date;
        return $this;
    }

    public function getAppointmentTime(): ?\DateTimeInterface
    {
        return $this->appointment_time;
    }

    public function setAppointmentTime(\DateTimeInterface $appointment_time): static
    {
        $this->appointment_time = $appointment_time;
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

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}
