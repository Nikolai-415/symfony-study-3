<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер для главной страницы
 */
class MainController extends AbstractController
{
    /**
     * Главная страница
     */
    public function index(): Response
    {
        return $this->render('main/index.html.twig');
    }
}
