<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\Microservice;
use App\Form\FavorisType;
use App\Repository\FavorisRepository;
use App\Repository\MicroserviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/favoris')]
class FavorisController extends AbstractController
{
    #[Route('/', name: 'favoris_index', methods: ['GET'])]
    public function index(FavorisRepository $favorisRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();

        $favoris = $paginator->paginate(
            $favorisRepository->findBy(['client' => $user], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('favoris/index.html.twig', [
            'favoris' => $favoris,
        ]);
    }

    #[Route('/add/{id}', name: 'favoris_new', methods: ['GET', 'POST'])]
    public function add(Microservice $microservice, EntityManagerInterface $entityManager, FavorisRepository $favorisRepository): Response
    {
        $user = $this->getUser();
        if (!$microservice) {
            dd('Aucun microservice');
        }

        if (!$user) return $this->json([
            'code' => 404,
            'message' => 'Vous devez être connecter!'
        ], 403);

        if ($microservice->isAddedByUser($user)) {

            $favori = $favorisRepository->findOneBy([
                'microservice' => $microservice,
                'client' => $user
            ]);

            $entityManager->remove($favori);
            $entityManager->flush();
            /*return $this->json([
                'code' => 200,
                'message' => 'Favoris supprimer avec succès',
            ], 200);*/
             

        } else {
            $favori = new Favoris;

            $favori->setMicroservice($microservice);
            $favori->setClient($user);
            $entityManager->persist($favori);
            $entityManager->flush();
            /*return $this->json([
                'code' => 200,
                'message' => 'Favoris ajouter avec succès',
            ], 200);*/
        }

        return $this->redirectToRoute('favoris_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'favoris_delete', methods: ['POST'])]
    public function delete(Request $request, Favoris $favori, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $favori->getId(), $request->request->get('_token'))) {
            $entityManager->remove($favori);
            $entityManager->flush();
        }

        return $this->redirectToRoute('favoris_index', [], Response::HTTP_SEE_OTHER);
    }
}
