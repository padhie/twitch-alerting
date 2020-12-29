<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class FrontendController extends AbstractController
{
    /**
     * @Route("/", name="frontend")
     */
    public function index(): Response
    {
        return $this->render('frontend/index.html.twig');
    }
}
