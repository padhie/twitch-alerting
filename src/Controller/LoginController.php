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

    #[Route('/login', name: 'login')]
    public function indexAction(): Response
    {
        return $this->redirect(
            $this->twitchApiWrapper->getAccessCodeUrl(
                array_merge(
                    TwitchAuthenticator::SCOPE_CHANNEL,
                    TwitchApiWrapper::SCOPE_PUBSUB
                )
            )
        );
    }

    #[Route("/twitch/get_access", name: "twitch_access")]
    public function getAccessAction(Request $request): Response
    {
        $code = $request->query->get('code') ?? '';
        $response = $this->twitchApiWrapper->getToken($code);

        return $this->handleAccessToken($request, $response->getAccessToken());
    }

    #[Route("/twitch/redirect", name: "twitch_redirect")]
    public function redirectAction(Request $request): Response
    {
        $oAuth = $request->get('access_token') ?? null;

        return $this->handleAccessToken($request, $oAuth);
    }

    private function handleAccessToken(Request $request, ?string $oAuth): Response
    {
        if ($oAuth === null) {
            return $this->redirectToRoute('frontend');
        }

        $this->twitchApiWrapper->checkAndUseRequestOAuth($oAuth);

        $validateModel = $this->twitchApiWrapper->validateByOAuth($oAuth);

        try {
            $twitchUser = $this->twitchApiWrapper->getUserByName($validateModel->getLogin());
        } catch (ClientException | \RuntimeException $exception) {
            $this->logger->error('error during login with twitch', [
                LoggerKeywords::CODE => 1686688033891,
                LoggerKeywords::EXCEPTION_MESSAGE => $exception->getMessage(),
                LoggerKeywords::EXCEPTION_CODE => $exception->getCode(),
            ]);
            $this->addFlash('error', 'Unexpected error during login. Please contact the administrator.');

            return $this->redirectToRoute('frontend');
        }

        $user = $this->userService->getOrCreateUserByTwitchUser($twitchUser, $oAuth);

        $session = $request->getSession();
        $session->set(UserService::SESSION_KEY_USER_ID, $user->getId());

        return $this->redirectToRoute('backend');
    }
}
