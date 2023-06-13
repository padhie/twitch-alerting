<?php

namespace App\Service;

use GuzzleHttp\Client;
use Padhie\TwitchApiBundle\Request\Authenticator\ValidateRequest;
use Padhie\TwitchApiBundle\Request\RequestGenerator;
use Padhie\TwitchApiBundle\Request\Users\GetUsersRequest;
use Padhie\TwitchApiBundle\Response\Authenticator\ValidateResponse;
use Padhie\TwitchApiBundle\Response\ResponseGenerator;
use Padhie\TwitchApiBundle\Response\Users\GetUsersResponse;
use Padhie\TwitchApiBundle\Response\Users\User;
use Padhie\TwitchApiBundle\TwitchAuthenticator;
use Padhie\TwitchApiBundle\TwitchClient;
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

    private TwitchClient $client;
    private TwitchAuthenticator $twitchAuthenticator;

    public function __construct(
        private readonly EnvironmentContainer $environmentContainer
    ) {
        $this->twitchAuthenticator = new TwitchAuthenticator(
            $this->environmentContainer->getTwitchClientId(),
            $this->environmentContainer->getTwitchRedirectUrl(),
        );
        $this->recreateClient();
    }

    public function checkAndUseRequestOAuth(Request $request): void
    {
        $session = $request->getSession();
        $oAuth = $session->get(self::SESSION_OAUTH_KEY);

        if ($session && $session->get(self::SESSION_OAUTH_KEY)) {
            $this->recreateClient($oAuth);
        }
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
    public function getAccessTokenUrl(array $scopeList = []): string
    {
        return $this->twitchAuthenticator->getAccessTokenUrl($scopeList);
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
