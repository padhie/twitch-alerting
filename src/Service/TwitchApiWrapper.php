<?php

namespace App\Service;

use App\Twitch\TokenRequest;
use App\Twitch\TokenResponse;
use GuzzleHttp\Client;
use Padhie\TwitchApiBundle\Request\Authenticator\ValidateRequest;
use Padhie\TwitchApiBundle\Request\ChannelPoints\GetCustomRewardRequest;
use Padhie\TwitchApiBundle\Request\RequestGenerator;
use Padhie\TwitchApiBundle\Request\Users\GetUsersRequest;
use Padhie\TwitchApiBundle\Response\Authenticator\ValidateResponse;
use Padhie\TwitchApiBundle\Response\ChannelPoints\CustomReward;
use Padhie\TwitchApiBundle\Response\ChannelPoints\GetCustomRewardResponse;
use Padhie\TwitchApiBundle\Response\ResponseGenerator;
use Padhie\TwitchApiBundle\Response\Users\GetUsersResponse;
use Padhie\TwitchApiBundle\Response\Users\User;
use Padhie\TwitchApiBundle\TwitchAuthenticator;
use Padhie\TwitchApiBundle\TwitchClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class TwitchApiWrapper
{
    public const SCOPE_PUBSUB = [
        'channel_editor',
        'bits:read',
        'channel:read:redemptions',
        'channel_subscriptions',
        'channel:moderate',
    ];

    private TwitchClient $client;
    private readonly TwitchAuthenticator $twitchAuthenticator;

    public function __construct(
        private readonly EnvironmentContainer $environmentContainer
    ) {
        $this->twitchAuthenticator = new TwitchAuthenticator(
            $this->environmentContainer->getTwitchClientId(),
            $this->environmentContainer->getTwitchRedirectUrl(),
        );
        $this->recreateClient();
    }

    public function checkAndUseRequestOAuth(?string $oAuth): void
    {
        if (is_string($oAuth) && trim($oAuth) !== '') {
            $this->recreateClient($oAuth);
        }
    }

    public function getToken(string $code): TokenResponse
    {
        $request = new TokenRequest(
            $this->environmentContainer->getTwitchClientId(),
            $this->environmentContainer->getTwitchClientSecret(),
            $code,
            $this->environmentContainer->getTwitchRedirectUrl()
        );
        $response = $this->client->send($request);

        if (!$response instanceof TokenResponse) {
            throw new \RuntimeException('invalid response', 1686763684545);
        }

        return $response;
    }

    public function validateByOAuth(string $oAuth): ValidateResponse
    {
        $this->recreateClient($oAuth);

        $request = new ValidateRequest();
        $response = $this->client->send($request);

        if (!$response instanceof ValidateResponse) {
            throw new \RuntimeException('invalid response', 1686681734274);
        }

        return $response;
    }

    /**
     * @param array<int, string> $scopeList
     */
    public function getAccessCodeUrl(array $scopeList = []): string
    {
        return $this->twitchAuthenticator->getAccessCodeUrl($scopeList);
    }

    public function getLoggedInUser(): User
    {
        $request = new GetUsersRequest(null, null);
        $response = $this->client->send($request);

        if (!$response instanceof GetUsersResponse) {
            throw new \RuntimeException('invalid response', 1686764217315);
        }

        $users = $response->getUsers();
        if (count($users) === 0) {
            throw new \RuntimeException('no user returned', 1686764219958);
        }

        return $users[0];
    }

    /**
     * @throws \RuntimeException
     */
    public function getUserByName(string $name): User
    {
        $request = new GetUsersRequest(null, $name);
        $response = $this->client->send($request);

        if (!$response instanceof GetUsersResponse) {
            throw new \RuntimeException('invalid response', 1686681540728);
        }

        $users = $response->getUsers();
        if (count($users) === 0) {
            throw new \RuntimeException('no user returned', 1686681544514);
        }

        return $users[0];
    }

    /** @return array<int, CustomReward> */
    public function getRewards(?int $id, ?string $login): array
    {
        if ($id === null && $login === null) {
            throw new \RuntimeException();
        }

        if ($id === null) {
            $request = new GetUsersRequest(null, $login);
            $response = $this->client->send($request);

            assert($response instanceof GetUsersResponse);
            $user = $response->getUsers()[0];

            assert($user instanceof User);
            $id = $user->getId();
        }

        $request = new GetCustomRewardRequest($id);
        $response = $this->client->send($request);
        assert($response instanceof GetCustomRewardResponse);

        return $response->getCustomRewards();
    }

    private function recreateClient(?string $oAuth = null): void
    {
        $oAuth = $oAuth ?? $this->environmentContainer->getTwitchAccessToken();

        $this->client = new TwitchClient(
            new Client(),
            new RequestGenerator($this->environmentContainer->getTwitchClientId(), $oAuth),
            new ResponseGenerator(),
        );
    }
}
