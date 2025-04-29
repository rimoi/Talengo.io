<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class PagesController extends AbstractController
{
    #[Route('/faqs', name: 'page_faqs')]
    public function faqs(): Response
    {
        return $this->render('pages/faqs.html.twig', [
            
        ]);
    }

    #[Route('/politique-de-confidentialite', name: 'page_politiques')]
    public function politiques(): Response
    {
        return $this->render('pages/politiques.html.twig', [
            
        ]);
    }

    #[Route('/mentions-legales', name: 'page_mentions')]
    public function mentions(): Response
    {
        return $this->render('pages/mentions.html.twig', [
            
        ]);
    }

    #[Route('/conditions-utilisations', name: 'page_conditions')]
    public function conditions(): Response
    {
        return $this->render('pages/conditions.html.twig', [
            
        ]);
    }

    #[Route('/comment-ca-marche', name: 'page_cmarche')]
    public function cmarche(): Response
    {
        return $this->render('pages/cmarche.html.twig', [
            
        ]);
    }
}
