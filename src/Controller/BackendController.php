<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AlertForm;
use App\Form\Model\Alert as AlertFormModel;
use App\Model\NotificationCollection;
use App\Repository\AlertRepository;
use App\Service\AlertFormHandler;
use App\Service\LoginService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BackendController extends AbstractController
{
    private LoginService $loginService;
    private UserService $userService;
    private AlertForm $alertForm;
    private AlertRepository $alertRepository;
    private AlertFormHandler $alertFormHandler;
    private TranslatorInterface $translator;

    public function __construct(
        LoginService $loginService,
        UserService $userService,
        AlertRepository $alertRepository,
        AlertForm $alertForm,
        AlertFormHandler $alertFormHandler,
        TranslatorInterface $translator
    ) {
        $this->loginService = $loginService;
        $this->userService = $userService;
        $this->alertForm = $alertForm;
        $this->alertRepository = $alertRepository;
        $this->alertFormHandler = $alertFormHandler;
        $this->translator = $translator;
    }

    /**
     * @Route("/admin", name="backend")
     */
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->loadUser($request);
        if ($user === null) {
            $this->addFlash('error', $this->translator->trans('error.no_login_found'));

            return $this->redirectToRoute('frontend');
        }

        $alertEntities = $this->alertRepository->findBy(
            ['user' => $user->getId()],
            ['id' => 'ASC']
        );

        $alertFormModel = AlertFormModel::createFromEntities($alertEntities);
        $form = $this->alertForm->generate(
            $alertFormModel,
            $this->generateUrl('backend')
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $notificationCollection = new NotificationCollection();
            $this->alertFormHandler->save($user, $form, $slugger, $alertFormModel, $notificationCollection);

            $this->addFlashMassages($notificationCollection);

            return $this->redirectToRoute('backend');
        }

        return $this->render(
            'backend/index.html.twig',
            [
                'maxIndex' => AlertForm::MAX_ITEMS - 1,
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    private function loadUser(Request $request): ?User
    {
        if (!$this->loginService->checkLogin($request)) {
            return null;
        }

        $login = $this->loginService->getTwitchLogin($request);
        if ($login === null) {
            return null;
        }

        return $this->userService->getUserByTwitchUser($login);
    }

    private function addFlashMassages(NotificationCollection $notificationCollection): void
    {
        foreach ($notificationCollection->getAllNotifications() as $notification) {
            $variables = [];
            foreach ($notification->variables as $key => $value) {
                $variables['%' . $key . '%'] = $value;
            }

            $this->addFlash(
                $notification->type,
                $this->translator->trans($notification->message, $variables)
            );
        }
    }
}
