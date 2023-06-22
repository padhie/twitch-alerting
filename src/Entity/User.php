<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Table(name: "users",
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: "twitchId", columns: ["twitch_id"]),
        new ORM\UniqueConstraint(name: "twitchLogin", columns: ["twitch_login"]),
    ]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
final class User
{
    #[ORM\Id]
    #[ORM\Column(type: "string", unique: true, nullable: false)]
    private string $id;

    #[ORM\Column(name: "twitch_id", type: "string", length: 255, nullable: false)]
    private string $twitchId;

    #[ORM\Column(name: "twitch_login", type: "string", length: 255, nullable: false)]
    private string $twitchLogin;

    #[ORM\Column(name: "twitch_oauth", type: "string", length: 255, nullable: false)]
    private string $twitchOAuth;

    public function __construct(string $twitchId, string $twitchLogin, string $twitchOAuth)
    {
        $this->id = Uuid::uuid4();
        $this->twitchId = $twitchId;
        $this->twitchLogin = $twitchLogin;
        $this->twitchOAuth = $twitchOAuth;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTwitchId(): string
    {
        return $this->twitchId;
    }

    public function getTwitchLogin(): string
    {
        return $this->twitchLogin;
    }

    public function getTwitchOAuth(): string
    {
        return $this->twitchOAuth;
    }

    public function setTwitchOAuth (string $twitchOAuth): self
    {
        $this->twitchOAuth = $twitchOAuth;

        return $this;
    }
}
