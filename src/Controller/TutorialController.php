<?php

namespace App\Controller;

use App\Model\TutorialItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TutorialController extends AbstractController
{
    /**
     * @Route("/tutorial", name="tutorial")
     */
    public function index(): Response
    {
        $tutorialList = [
            new TutorialItem(1, 'asset/img/page/tutorial/login.png'),
            new TutorialItem(2, 'asset/img/page/tutorial/backend.png'),
            new TutorialItem(3, 'asset/img/page/tutorial/save.png'),
            new TutorialItem(4, 'asset/img/page/tutorial/go_to_view.png'),
            new TutorialItem(5, 'asset/img/page/tutorial/view.png'),
        ];

        return $this->render(
            'tutorial/index.html.twig',
            [
                'tutorialList' => $tutorialList,
            ]
        );
    }
}
