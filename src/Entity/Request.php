<?php

namespace App\Entity;

use App\Entity\DeviceRequestItem;
use App\Entity\Report;
use App\Entity\RequestStatus;
use App\Enum\Status;
use App\Repository\RequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Request
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $id = null;

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

    #[ORM\Column(length: 32, options: ['default' => 'Pending'])]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $time_in = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $time_out = null;

    #[ORM\OneToMany(mappedBy: 'request', targetEntity: DeviceRequestItem::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $deviceRequestItems;

    #[ORM\OneToMany(mappedBy: 'request', targetEntity: RequestStatus::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $statuses;

    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'requests')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Report $report = null;

    public function __construct()
    {
        $this->deviceRequestItems = new ArrayCollection();
        $this->statuses = new ArrayCollection();
    }

    public function getId(): ?string
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    /**
     * @return Collection<int, DeviceRequestItem>
     */
    public function getDeviceRequestItems(): Collection
    {
        return $this->deviceRequestItems;
    }

    public function addDeviceRequestItem(DeviceRequestItem $deviceRequestItem): static
    {
        if (!$this->deviceRequestItems->contains($deviceRequestItem)) {
            $this->deviceRequestItems->add($deviceRequestItem);
            $deviceRequestItem->setRequest($this);
        }

        return $this;
    }

    public function removeDeviceRequestItem(DeviceRequestItem $deviceRequestItem): static
    {
        if ($this->deviceRequestItems->removeElement($deviceRequestItem)) {
            if ($deviceRequestItem->getRequest() === $this) {
                $deviceRequestItem->setRequest($this);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RequestStatus>
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function addStatus(RequestStatus $status): static
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
            $status->setRequest($this);
        }

        return $this;
    }

    public function removeStatus(RequestStatus $status): static
    {
        if ($this->statuses->removeElement($status)) {
            if ($status->getRequest() === $this) {
                $status->setRequest($this);
            }
        }

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    #[ORM\PrePersist]
    public function setTimeInValue(): void
    {
        if ($this->time_in === null) {
            $this->time_in = new \DateTimeImmutable();
        }

        if ($this->status === null) {
            $this->status = Status::PENDING->value;
        }

        if ($this->id === null) {
            $this->id = bin2hex(random_bytes(16));
        }
    }
}
