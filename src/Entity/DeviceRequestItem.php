<?php

namespace App\Entity;

use App\Repository\DeviceRequestItemRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Request;

#[ORM\Entity(repositoryClass: DeviceRequestItemRepository::class)]
class DeviceRequestItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Request::class, inversedBy: 'deviceRequestItems')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id')]
    private ?Request $request = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
}
