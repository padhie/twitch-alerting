<?php

namespace App\Service;

use Padhie\TwitchApiBundle\Exception\ApiErrorException;
use Padhie\TwitchApiBundle\Exception\UserNotExistsException;
use Padhie\TwitchApiBundle\Model\TwitchChannelSubscriptions;
use Padhie\TwitchApiBundle\Model\TwitchStream;
use Padhie\TwitchApiBundle\Model\TwitchUser;
use Padhie\TwitchApiBundle\Model\TwitchValidate;
use Padhie\TwitchApiBundle\Service\TwitchApiService;
use Symfony\Component\HttpFoundation\Request;

final class TwitchApiWrapper
{
    public const SCOPE_PUBSUB = [
        'channel_editor',
        'bits:read',
        'channel:read:redemptions',
        'channel_subscriptions',
        'channel:moderate',
    ];
    public const SESSION_OAUTH_KEY = 'twitchOAuth';
    public const SESSION_LOGIN = 'twitchLogin';

    private TwitchApiService $twitchApiService;

    public function __construct(EnvironmentContainer $environmentContainer)
    {
        $this->twitchApiService = new TwitchApiService(
            $environmentContainer->getTwitchClientId(),
            $environmentContainer->getTwitchClientSecret(),
            $environmentContainer->getTwitchRedirectUrl());
        $this->twitchApiService->setOAuth($environmentContainer->getTwitchAccessToken());
    }

    public function checkAndUseRequestOAuth(Request $request): void
    {
        $session = $request->getSession();
        $oAuth = $session->get(self::SESSION_OAUTH_KEY);

        if ($session && $session->get(self::SESSION_OAUTH_KEY)) {
            $this->twitchApiService->setOAuth($oAuth);
        }
    }

    public function validateByOAuth(string $oAuth): TwitchValidate
    {
        $this->twitchApiService->setOAuth($oAuth);

        return $this->twitchApiService->validate();
    }

    /**
     * @param array<int, string> $scopeList
     */
    public function getAccessTokenUrl(array $scopeList = []): string
    {
        return $this->twitchApiService->getAccessTokenUrl($scopeList);
    }

    /**
     * @throws ApiErrorException
     * @throws UserNotExistsException
     */
    public function getUserByName(string $name): TwitchUser
    {
        return $this->twitchApiService->getUserByName($name);
    }

    /**
     * @throws ApiErrorException
     */
    public function getStream(int $channelId = 0): ?TwitchStream
    {
        return $this->twitchApiService->getStream($channelId);
    }

    /**
     * @throws ApiErrorException
     */
    public function getChannelSubscriber(int $channelId = 0): TwitchChannelSubscriptions
    {
        return $this->twitchApiService->getChannelSubscriber($channelId);
    }
}
