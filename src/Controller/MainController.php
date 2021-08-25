<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    public function index(): Response
    {
        if ($this->isGranted('ROLE_LIST_VIEW')) {
            return $this->redirectToRoute('data_list');
        }
        return $this->render('main/index.html.twig');
    }
}
