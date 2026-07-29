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

    #[ORM\Column(length: 9)]
    private ?string $student_id = null;

    #[ORM\Column(length: 128)]
    private ?string $full_name = null;

    #[ORM\Column(length: 12)]
    private ?string $contact_no = null;

    #[ORM\Column(length: 255)]
    private ?string $program = null;

    #[ORM\Column(length: 128)]
    private ?string $request_type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $time_in = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $time_out = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentId(): ?string
    {
        return $this->student_id;
    }

    public function setStudentId(string $student_id): static
    {
        $this->student_id = $student_id;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): static
    {
        $this->full_name = $full_name;

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

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function setProgram(string $program): static
    {
        $this->program = $program;

        return $this;
    }

    public function getRequestType(): ?string
    {
        return $this->request_type;
    }

    public function setRequestType(string $request_type): static
    {
        $this->request_type = $request_type;

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
}
