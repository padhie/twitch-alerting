<?php

namespace App\Controller;

use App\Service\TwitchApiWrapper;
use App\Service\UserService;
use Padhie\TwitchApiBundle\Service\TwitchApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LoginController extends AbstractController
{
    private TwitchApiWrapper $twitchApiWrapper;
    private UserService $userService;

    public function __construct(TwitchApiWrapper $twitchApiWrapper, UserService $userService)
    {
        $this->twitchApiWrapper = $twitchApiWrapper;
        $this->userService = $userService;
    }

    /**
     * @Route("/login", name="login")
     */
    public function indexAction(): Response
    {
        return $this->redirect(
            $this->twitchApiWrapper->getAccessTokenUrl(
                array_merge(
                    TwitchApiService::SCOPE_CHANNEL,
                    TwitchApiWrapper::SCOPE_PUBSUB
                )
            )
        );
    }

    /**
     * @Route("/twitch/get_access", name="twitch_access")
     */
    public function getAccessAction(): Response
    {
        return $this->render('twitch/access.html.twig');
    }

    /**
     * @Route("/twitch/redirect", name="twitch_redirect")
     */
    public function redirectAction(Request $request): Response
    {
        $oAuth = $request->get('access_token');
        if ($oAuth === null) {
            return $this->redirectToRoute('frontend');
        }

        $this->twitchApiWrapper->checkAndUseRequestOAuth($request);

        $session = $request->getSession();
        $validateModel = $this->twitchApiWrapper->validateByOAuth($oAuth);
        $session->set(TwitchApiWrapper::SESSION_LOGIN, $validateModel->getLogin());

        $twitchUser = $this->twitchApiWrapper->getUserByName($validateModel->getLogin());
        $this->userService->getOrCreateUserByTwitchUser($twitchUser, $oAuth);

        return $this->redirectToRoute('backend');
    }

}
