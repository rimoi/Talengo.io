<?php

namespace App\Entity;

use App\Entity\Traits\Timestamp;
use App\Repository\DisponibiliteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisponibiliteRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Disponibilite
{
    use Timestamp;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureOuverture = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureCloture = null;

    #[ORM\ManyToOne(inversedBy: 'disponibilites')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Microservice $service = null;

    #[ORM\Column(type: 'array', nullable: true)]
    private $jours = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeureOuverture(): ?\DateTimeInterface
    {
        return $this->heureOuverture;
    }

    public function setHeureOuverture(?\DateTimeInterface $heureOuverture): self
    {
        $this->heureOuverture = $heureOuverture;

        return $this;
    }

    public function getHeureCloture(): ?\DateTimeInterface
    {
        return $this->heureCloture;
    }

    public function setHeureCloture(?\DateTimeInterface $heureCloture): self
    {
        $this->heureCloture = $heureCloture;

        return $this;
    }

    public function getService(): ?Microservice
    {
        return $this->service;
    }

    public function setService(?Microservice $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function __toString()
    {
        return $this->getId();
    }

    public function getJours()
    {
        return $this->jours;
    }

    public function setJours(?array $jours): self
    {
        $this->jours = $jours;

        return $this;
    }
}
