<?php

namespace App\Entity;

use App\Repository\RequestStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestStatusRepository::class)]
class RequestStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Request $request = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $compliance_date = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $remarks = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): static
    {
        $this->request = $request;

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

    public function getComplianceDate(): ?\DateTimeImmutable
    {
        return $this->compliance_date;
    }

    public function setComplianceDate(?\DateTimeImmutable $compliance_date): static
    {
        $this->compliance_date = $compliance_date;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): static
    {
        $this->remarks = $remarks;

        return $this;
    }
}
