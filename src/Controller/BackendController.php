<?php

namespace App\Controller;

use App\Form\AlertForm;
use App\Form\Model\Alert as AlertFormModel;
use App\Repository\AlertRepository;
use App\Service\AlertFormHandler;
use App\Service\LoginService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class BackendController extends AbstractController
{
    private LoginService $loginService;
    private UserService $userService;
    private AlertForm $alertForm;
    private AlertRepository $alertRepository;
    private AlertFormHandler $alertFormHandler;

    public function __construct(
        LoginService $loginService,
        UserService $userService,
        AlertRepository $alertRepository,
        AlertForm $alertForm,
        AlertFormHandler $alertFormHandler
    ) {
        $this->loginService = $loginService;
        $this->userService = $userService;
        $this->alertForm = $alertForm;
        $this->alertRepository = $alertRepository;
        $this->alertFormHandler = $alertFormHandler;
    }

    /**
     * @Route("/admin", name="backend")
     */
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        if (!$this->loginService->checkLogin($request)) {
            return $this->redirectToRoute('frontend');
        }

        $login = $this->loginService->getTwitchLogin($request);
        if ($login === null) {
            return $this->redirectToRoute('frontend');
        }

        $user = $this->userService->getOrCreateUserByTwitchUser($login);
        $alertEntities = $this->alertRepository->findBy([
            'user' => $user->getId(),
            'active' => true,
        ]);

        $alertFormModel = AlertFormModel::createFromEntities($alertEntities);
        $form = $this->alertForm->generate($alertFormModel);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->alertFormHandler->save($user, $form, $slugger, $alertFormModel);
        }

        return $this->render(
            'backend/index.html.twig',
            [
                'maxIndex' => AlertForm::MAX_ITEMS - 1,
                'form' => $form->createView(),
            ]
        );
    }
}
