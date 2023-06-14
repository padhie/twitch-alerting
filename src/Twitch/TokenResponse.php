<?php

declare(strict_types=1);

namespace App\Twitch;

use Padhie\TwitchApiBundle\Response\ResponseInterface;

final class TokenResponse implements ResponseInterface
{
    private string $accessToken;
    private int $expiresIn;
    private string $refreshToken;
    /** @var array<int, string> */
    private array $scope;
    private string $tokenType;

    public static function createFromArray (array $data): ResponseInterface
    {
        $self = new self();

        $self->accessToken = $data['access_token'] ?? '';
        $self->expiresIn = $data['expires_in'] ?? 0;
        $self->refreshToken = $data['refresh_token'] ?? '';
        $self->scope = $data['scope'] ?? [];
        $self->tokenType = $data['token_type'] ?? '';

        return $self;
    }

    public function jsonSerialize (): array
    {
        return [
            'access_token' => $this->accessToken,
            'expires_in' => $this->expiresIn,
            'refresh_token' => $this->refreshToken,
            'scope' => $this->scope,
            'token_type' => $this->tokenType,
        ];
    }

    public function getAccessToken (): string
    {
        return $this->accessToken;
    }

    public function getExpiresIn (): int
    {
        return $this->expiresIn;
    }

    public function getRefreshToken (): string
    {
        return $this->refreshToken;
    }

    public function getScope (): array
    {
        return $this->scope;
    }

    public function getTokenType (): string
    {
        return $this->tokenType;
    }
}