<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Created
{

   #[ORM\Column(type: 'datetime')]
   private $created;

   public function getCreated(): ?\DateTimeInterface
   {
      return $this->created;
   }

   public function setCreated(\DateTimeInterface $created): self
   {
      $this->created = $created;

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
   }
}
