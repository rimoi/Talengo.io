<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{

   #[ORM\Column(type: 'datetime')]
   private $created;

   #[ORM\Column(type: 'datetime')]
   private $updated;

   public function getCreated(): ?\DateTimeInterface
   {
      return $this->created;
   }

   public function setCreated(\DateTimeInterface $created): self
   {
      $this->created = $created;

      return $this;
   }

   public function getUpdated(): ?\DateTimeInterface
   {
      return $this->updated;
   }

   public function setUpdated(\DateTimeInterface $updated): self
   {
      $this->updated = $updated;

      return $this;
   }

   /**
    * Permet d'automatiser la date et de création et de modification
    */
   #[ORM\PrePersist]
   #[ORM\PreUpdate]
   public function updateTimestamps(): void
   {
      if ($this->getCreated() === null) {
         $this->setCreated(new \DateTimeImmutable());
      }
      $this->setUpdated(new \DateTimeImmutable());
   }
}
