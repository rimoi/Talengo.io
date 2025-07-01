<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentCaMarcheController extends AbstractController
{
    #[Route('/how-it-works', name: 'app_comment_ca_marche')]
    public function index(): Response
    {
        return $this->render('comment_ca_marche/index.html.twig');
    }
}
