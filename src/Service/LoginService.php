<?php

namespace App\Service;

use App\Logger\LoggerKeywords;
use Padhie\TwitchApiBundle\Response\Users\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

final class LoginService
{
    public function __construct(
        private readonly TwitchApiWrapper $twitchApiWrapper,
        private readonly LoggerInterface $logger
    ) {
    }

    public function checkLogin(Request $request): bool
    {
        $session = $request->getSession();
        $oAuth = $session->get(TwitchApiWrapper::SESSION_OAUTH_KEY);
        $login = $session->get(TwitchApiWrapper::SESSION_LOGIN);

        return $login !== null
            || $oAuth !== null;
    }

    public function getTwitchLogin(Request $request): ?User
    {
        $session = $request->getSession();
        $login = $session->get(TwitchApiWrapper::SESSION_LOGIN);
        $oAuth = $session->get(TwitchApiWrapper::SESSION_OAUTH_KEY);

        if (!$this->checkLogin($request)) {
            return null;
        }

        if ($login === null && $oAuth !== null) {
            $session = $request->getSession();
            $validateModel = $this->twitchApiWrapper->validateByOAuth($oAuth);
            $login = $validateModel->getLogin();
            $session->set(TwitchApiWrapper::SESSION_LOGIN, $login);
        }

        try {
            return $this->twitchApiWrapper->getUserByName($login);
        } catch (\RuntimeException $exception) {
            $this->logger->error(
                'error during login with twitch',
                [
                    LoggerKeywords::CODE => 1686687072003,
                    LoggerKeywords::EXCEPTION => $exception,
                    'login' => $login,
                ]
            );

            return null;
        }
    }
}