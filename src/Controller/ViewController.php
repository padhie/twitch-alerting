<?php

namespace App\Controller;

use App\Repository\AlertRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ViewController extends AbstractController
{
    private AlertRepository $alertRepository;
    private UserRepository $userRepository;

    public function __construct(AlertRepository $alertRepository, UserRepository $userRepository)
    {
        $this->alertRepository = $alertRepository;
        $this->userRepository = $userRepository;
    }

    #[Route("/view/{userId}/", name: "view")]
    public function index(string $userId, Request $request): Response
    {
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);
        if ($user === null) {
            $this->redirect('frontend');
        }

        $alerts = $this->alertRepository->findBy([
            'user' => $user,
            'active' => true,
        ]);

        return $this->render('view/index.html.twig', [
            'alerts' => $alerts,
            'user' => $user,
            'baseFileUrl' => '/sound/' . $user->getId() ?? 0,
            'debug' => (bool) $request->get('debug', false),
        ]);
    }
}
