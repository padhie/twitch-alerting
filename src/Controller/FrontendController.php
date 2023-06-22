<?php

namespace App\Controller;

use App\Service\TwitchApiWrapper;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class FrontendController extends AbstractController
{
    public function __construct(
        private readonly TwitchApiWrapper $twitchApiWrapper,
        private readonly UserService $userService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route("/", name: "frontend")]
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }

    #[Route("/test", name: "test")]
    public function test(Request $request): Response
    {
        $session = $request->getSession();

        $userUuid = $session->get(UserService::SESSION_KEY_USER_ID);
        dump($userUuid);
        $user = $this->userService->find($userUuid ?? '');
        if ($user === null) {
            $this->addFlash('error', 'no user found');

            return $this->render('frontend/index.html.twig');
        }

        $this->twitchApiWrapper->checkAndUseRequestOAuth($user->getTwitchOAuth());
        $rewards = $this->twitchApiWrapper->getRewards($user->getTwitchId(), $user->getTwitchLogin());
        dump($rewards);

        return $this->render('frontend/index.html.twig');
    }
}
