<?php

namespace App\Service;

use Padhie\TwitchApiBundle\Exception\ApiErrorException;
use Padhie\TwitchApiBundle\Exception\UserNotExistsException;
use Padhie\TwitchApiBundle\Model\TwitchChannel;
use Padhie\TwitchApiBundle\Model\TwitchChannelSubscriptions;
use Padhie\TwitchApiBundle\Model\TwitchFollower;
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
    private const ACCESS_DENIED_EXCEPTION_MESSAGE = 'Unable to access channel subscribers of';

    /** @var TwitchApiService */
    private $twitchApiService;

    public function __construct(string $twitchClientId, string $twitchSecret, string $twitchRedirectUrl, string $twitchAccessToken)
    {
        $this->twitchApiService = new TwitchApiService($twitchClientId, $twitchSecret, $twitchRedirectUrl);
        $this->twitchApiService->setOAuth($twitchAccessToken);
    }

    public function checkAndUseRequestOAuth(Request $request): void
    {
        $session = $request->getSession();
        if ($session && $session->get(self::SESSION_OAUTH_KEY)) {
            $this->twitchApiService->setOAuth($session->get(self::SESSION_OAUTH_KEY));
        }
    }

    public function validateByOAuth(string $oAuth): TwitchValidate
    {
        $this->twitchApiService->setOAuth($oAuth);

        return $this->twitchApiService->validate();
    }

    public function isAccessException(ApiErrorException $exception): bool
    {
        return strpos($exception->getMessage(), self::ACCESS_DENIED_EXCEPTION_MESSAGE) !== false;
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
    public function getChannelById(int $channelId = 0): TwitchChannel
    {
        return $this->twitchApiService->getChannelById($channelId);
    }

    public function isUserFollowingChannel(int $userId = 0, int $channelId = 0): bool
    {
        return $this->twitchApiService->isUserFollowingChannel($userId, $channelId);
    }

    public function getUserFollowingChannel(): TwitchFollower
    {
        return $this->twitchApiService->getUserFollowingChannel();
    }

    /**
     * @throws ApiErrorException
     */
    public function getEmoticonImageListByEmoteiconSets(string $emoticonsets): array
    {
        return $this->twitchApiService->getEmoticonImageListByEmoteiconSets($emoticonsets);
    }

    /**
     * @throws ApiErrorException
     */
    public function getChannelSubscriber(int $channelId = 0): TwitchChannelSubscriptions
    {
        return $this->twitchApiService->getChannelSubscriber($channelId);
    }
}
