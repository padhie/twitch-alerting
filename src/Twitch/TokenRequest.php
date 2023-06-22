<?php

declare(strict_types=1);

namespace App\Twitch;

use Padhie\TwitchApiBundle\Request\RequestInterface;

final class TokenRequest implements RequestInterface
{
    public function __construct (
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $code,
        private readonly string $redirectUrl,
    ) {}

    public function getMethod (): string
    {
        return RequestInterface::METHOD_POST;
    }

    public function getUrl (): string
    {
        return 'https://id.twitch.tv/oauth2/token';
    }

    public function getHeader (): array
    {
        return [];
    }

    public function getParameter (): array
    {
        return [];
    }

    public function getBody (): array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $this->code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    public function getResponseClass (): string
    {
        return TokenResponse::class;
    }
}