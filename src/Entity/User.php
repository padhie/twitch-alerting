<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="twitchId", columns={"twitch_id"}),
 *        @ORM\UniqueConstraint(name="twitchLogin", columns={"twitch_login"})
 *    }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @ORM\Column(name="twitch_id", type="string", length=255, nullable=false)
     */
    private string $twitchId;

    /**
     * @ORM\Column(name="twitch_login", type="string", length=255, nullable=false)
     */
    private string $twitchLogin;

    /**
     * @ORM\Column(name="twitch_oauth", type="string", length=255, nullable=false)
     */
    private string $twitchOAuth;

    public function __construct(string $twitchId, string $twitchLogin, string $twitchOAuth)
    {
        $this->twitchId = $twitchId;
        $this->twitchLogin = $twitchLogin;
        $this->twitchOAuth = $twitchOAuth;
    }

    public function getId(): ?string
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
}
