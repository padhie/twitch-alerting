<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="twitchUuid", columns={"twitch_uuid"}),
 *        @ORM\UniqueConstraint(name="twitchLogin", columns={"twitch_login"})
 *    }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
final class User
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @ORM\Column(name="twitch_uuid", type="string", length=255)
     */
    private string $twitchUuid;

    /**
     * @ORM\Column(name="twitch_login", type="string", length=255)
     */
    private string $twitchLogin;

    public function __construct(string $twitchUuid, string $twitchLogin)
    {
        $this->twitchUuid = $twitchUuid;
        $this->twitchLogin = $twitchLogin;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTwitchUuid(): string
    {
        return $this->twitchUuid;
    }

    public function getTwitchLogin(): string
    {
        return $this->twitchLogin;
    }
}
