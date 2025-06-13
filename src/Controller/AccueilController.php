<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use App\Repository\MicroserviceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'accueil', methods: ['POST', 'GET'])]
    public function index(
        MicroserviceRepository $microserviceRepository,
    ): Response
    {
        return $this->render('accueil/index.html.twig', [
            'microservices' => $microserviceRepository->sortByAvis(8),
        ]);
    }

    #[Route('/top-services', name: 'list', methods: ['GET'])]
    #[Route('/search-services', name: 'search_list', methods: ['POST'])]
    public function topService(
        Request $request,
        MicroserviceRepository $microserviceRepository,
        PaginatorInterface $paginator
    ): Response
    {
        if ($request->get('q') === '2') {
            // Dernières nouveauté
            $services = $microserviceRepository->findBy([], ['id' => 'DESC']);
        } elseif ($request->get('q') === '1') {
            // Meuilleur ventes

            // A voir cela une fois connaitre comment on créer une commande
            $services = $microserviceRepository->sortByAvis();

        } elseif ($request->attributes->get('_route') == 'search_list') {
            $services = $microserviceRepository->search($request);
        } else {
            $services = $microserviceRepository->sortByAvis();
        }

        $services = $paginator->paginate(
            $services,
            $request->query->getInt('page', 1),
            48
        );

        return $this->render('accueil/list.html.twig', [
            'microservices' => $services,
        ]);
    }

    #[Route('/list-categories', name: 'list_categories', methods: ['POST', 'GET'])]
    public function listCategories(
        Request $request,
        CategorieRepository $categorieRepository
    ): Response
    {
        $slug = $request->get('slug');
        $categories = $categorieRepository->findAll();

        return $this->render('partials/base/_list_categories.html.twig', [
            'categories' => $categories,
            'slug' =>   $slug,
            'is_footer' => $request->get('isFooter'),
        ]);
    }

    #[Route('/show-service/{slug}', name: 'show_service', methods: ['POST', 'GET'])]
    public function showService(
        Categorie $categorie,
        Request $request,
        MicroserviceRepository $microserviceRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $services = $paginator->paginate(
            $microserviceRepository->findBy(['categorie' => $categorie], ['id' => 'DESC']),
            $request->query->getInt('page', 1),
            48
        );

        return $this->render('accueil/show_service.html.twig', [
            'microservices' => $services,
            'categorie' => $categorie
        ]);
    }
}
