<?php

namespace App\Service;

class EnvironmentContainer
{
    private string $twitchClientId;
    private string $twitchClientSecret;
    private string $twitchRedirectUrl;
    private string $twitchAccessToken;
    private string $dataDirectory;

    public function __construct(
        string $twitchClientId,
        string $twitchClientSecret,
        string $twitchRedirectUrl,
        string $twitchAccessToken,
        string $dataDirectory
    ) {
        $this->twitchClientId = $twitchClientId;
        $this->twitchClientSecret = $twitchClientSecret;
        $this->twitchRedirectUrl = $twitchRedirectUrl;
        $this->twitchAccessToken = $twitchAccessToken;
        $this->dataDirectory = $dataDirectory;
    }

    public function getTwitchClientId(): string
    {
        return $this->twitchClientId;
    }

    public function getTwitchClientSecret(): string
    {
        return $this->twitchClientSecret;
    }

    public function getTwitchRedirectUrl(): string
    {
        return $this->twitchRedirectUrl;
    }

    public function getTwitchAccessToken(): string
    {
        return $this->twitchAccessToken;
    }

    public function getDataDirectory(): string
    {
        return $this->dataDirectory;
    }
}
