<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $interested_party = null;

    #[ORM\Column(length: 255)]
    private ?string $requirement = null;

    #[ORM\Column(length: 12)]
    private ?string $contact_no = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_entered = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $time_in = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $time_out = null;

    #[ORM\Column(length: 50)]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInterestedParty(): ?string
    {
        return $this->interested_party;
    }

    public function setInterestedParty(string $interested_party): static
    {
        $this->interested_party = $interested_party;

        return $this;
    }

    public function getRequirement(): ?string
    {
        return $this->requirement;
    }

    public function setRequirement(string $requirement): static
    {
        $this->requirement = $requirement;

        return $this;
    }

    public function getContactNo(): ?string
    {
        return $this->contact_no;
    }

    public function setContactNo(string $contact_no): static
    {
        $this->contact_no = $contact_no;

        return $this;
    }

    public function getDateEntered(): ?\DateTimeImmutable
    {
        return $this->date_entered;
    }

    public function setDateEntered(\DateTimeImmutable $date_entered): static
    {
        $this->date_entered = $date_entered;

        return $this;
    }

    public function getTimeIn(): ?\DateTimeImmutable
    {
        return $this->time_in;
    }

    public function setTimeIn(\DateTimeImmutable $time_in): static
    {
        $this->time_in = $time_in;

        return $this;
    }

    public function getTimeOut(): ?\DateTimeImmutable
    {
        return $this->time_out;
    }

    public function setTimeOut(?\DateTimeImmutable $time_out): static
    {
        $this->time_out = $time_out;

        return $this;
    }

    #[ORM\PrePersist] // 2. Triggers automatically on INSERT
    public function setTimeInAutomatically(): void
    {
        if ($this->time_in === null) {
            $this->time_in = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate] // 3. Triggers automatically on UPDATE
    public function setTimeOutAutomatically(): void
    {
        // Automatically sets timeOut if it hasn't been set yet
        if ($this->time_out === null) {
            $this->time_out = new \DateTimeImmutable();
        }
    }
}
