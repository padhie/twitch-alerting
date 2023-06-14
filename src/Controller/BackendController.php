<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AlertListForm;
use App\Form\Model\AlertList as AlertFormModel;
use App\Model\NotificationCollection;
use App\Repository\AlertRepository;
use App\Service\AlertListFormHandler;
use App\Service\TwitchApiWrapper;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BackendController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AlertRepository $alertRepository,
        private readonly AlertListForm $alertForm,
        private readonly AlertListFormHandler $alertListFormHandler,
        private readonly TranslatorInterface $translator
    ) {
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
            $this->alertListFormHandler->save($user, $form, $slugger, $alertFormModel, $notificationCollection);

            $this->addFlashMassages($notificationCollection);

            return $this->redirectToRoute('backend');
        }

        return $this->render(
            'backend/index.html.twig',
            [
                'maxIndex' => AlertListForm::MAX_ITEMS - 1,
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    private function loadUser(Request $request): ?User
    {
        $session = $request->getSession();
        $id = $session->get(TwitchApiWrapper::SESSION_ID);
        $login = $session->get(TwitchApiWrapper::SESSION_LOGIN);

        return $this->userService->getUserByTwitchUserData($id, $login);
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
