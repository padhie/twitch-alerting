<?php

namespace App\Service;

use Padhie\TwitchApiBundle\Model\TwitchUser;
use Symfony\Component\HttpFoundation\Request;

final class LoginService
{
    private TwitchApiWrapper $twitchApiWrapper;

    public function __construct(TwitchApiWrapper $twitchApiWrapper)
    {

        $this->twitchApiWrapper = $twitchApiWrapper;
    }

    public function checkLogin(Request $request): bool
    {
        $session = $request->getSession();
        $oAuth = $session->get(TwitchApiWrapper::SESSION_OAUTH_KEY);
        $login = $session->get(TwitchApiWrapper::SESSION_LOGIN);

        return $login !== null
            || $oAuth !== null;
    }

    public function getTwitchLogin(Request $request): ?TwitchUser
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

        return $this->twitchApiWrapper->getUserByName($login);
    }
}