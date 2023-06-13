<?php

namespace App\Controller;

use App\Logger\LoggerKeywords;
use App\Service\TwitchApiWrapper;
use App\Service\UserService;
use GuzzleHttp\Exception\ClientException;
use Padhie\TwitchApiBundle\TwitchAuthenticator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LoginController extends AbstractController
{
    public function __construct(
        private readonly TwitchApiWrapper $twitchApiWrapper,
        private readonly UserService $userService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @Route("/login", name="login")
     */
    public function indexAction(): Response
    {
        return $this->redirect(
            $this->twitchApiWrapper->getAccessTokenUrl(
                array_merge(
                    TwitchAuthenticator::SCOPE_CHANNEL,
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

        try {
            $twitchUser = $this->twitchApiWrapper->getUserByName($validateModel->getLogin());
        } catch (ClientException | \RuntimeException $exception) {
            $this->logger->error('error during login with twitch', [
                LoggerKeywords::CODE => 1686688033891,
                LoggerKeywords::EXCEPTION => $exception,
            ]);
            $this->addFlash('error', 'Unexpected error during login. Please contact the administrator.');

            return $this->redirectToRoute('frontend');
        }

        $this->userService->getOrCreateUserByTwitchUser($twitchUser, $oAuth);

        return $this->redirectToRoute('backend');
    }

}
