<?php

namespace App\Entity;

use App\Repository\CaisseRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Timestamp;

#[ORM\Entity(repositoryClass: CaisseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Caisse
{
    use Timestamp;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $soldeDisponible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoldeDisponible(): ?float
    {
        return $this->soldeDisponible;
    }

    public function setSoldeDisponible(float $soldeDisponible): self
    {
        $this->soldeDisponible = $soldeDisponible;

        return $this;
    }
}
