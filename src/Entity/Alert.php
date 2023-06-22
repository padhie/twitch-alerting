<?php

namespace App\Entity;

use App\Repository\AlertRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "alerts")]
#[ORM\UniqueConstraint(name: "user_name", columns: ["user_id", "name"])]
#[ORM\Entity(repositoryClass: AlertRepository::class)]
final class Alert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private User $user;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: "file", type: "string", length: 255, nullable: false)]
    private string $file;

    #[ORM\Column(name: "active", type: "boolean", nullable: false, options: ['default' => false])]
    private bool $active = false;

    public function __construct(User $user, string $name, string $file = '')
    {
        $this->user = $user;
        $this->name = $name;
        $this->file = $file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $field): self
    {
        $this->file = $field;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
