<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\UserRepository;
use Imagine\Image\Format;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitmapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitmap', defaults: ["_format" => 'xml'])]
    public function index(Request $request, MicroserviceRepository $microserviceRepository, CategorieRepository $categorieRepository, UserRepository $userRepository): Response
    {
        $hostname = $request->getSchemeAndHttpHost();
        
        $urls = [];

        $urls[] = ['loc' => $this->generateUrl('accueil')];
        $urls[] = ['loc' => $this->generateUrl('microservices')];
        $urls[] = ['loc' => $this->generateUrl('page_faqs')];
        $urls[] = ['loc' => $this->generateUrl('app_contact')];
        $urls[] = ['loc' => $this->generateUrl('page_politiques')];
        $urls[] = ['loc' => $this->generateUrl('page_mentions')];
        $urls[] = ['loc' => $this->generateUrl('page_conditions')];
        $urls[] = ['loc' => $this->generateUrl('page_cmarche')];

        foreach($categorieRepository->findAll() as $categorie) {
            $urls[] = [
                'loc' => $this->generateUrl('show_service', ['slug' => $categorie->getSlug()]),
                'lastmod' => $categorie->getCreated()->format('Y-m-d')
            ];
        }

        foreach($userRepository->findByRole('ROLE_VENDEUR') as $user) {

            $urls[] = [
                'loc' => $this->generateUrl('vendeur_profil', ['nameUrl' => $user->getNameUrl()]),
                'lastmod' => $user->getCreated()->format('Y-m-d')
            ];
        }

        foreach($microserviceRepository->findAll() as $microservice) {

            $lastMod = $microservice->getUpdated() ? $microservice->getUpdated()->format('Y-m-d') : null;

            if (!$lastMod) {
                $lastMod = $microservice->getCreated() ? $microservice->getCreated()->format('Y-m-d') : null;
            }

            $urls[] = [
                'loc' => $this->generateUrl('microservice_details', ['slug' => $microservice->getSlug()]),
                'lastmod' => $lastMod
            ];
        }

        $response = new Response(
            $this->renderView('sitmap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname,
            ]), 200
        );

        $response->headers->set('content-type', 'text/xml');

        return $response;
    }
}
