<?php

namespace App\Controller;

use App\Entity\Suivis;
use App\Entity\User;
use App\Form\SuivisType;
use App\Repository\SuivisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/suivis')]
class SuivisController extends AbstractController
{
    #[Route('/', name: 'suivis_index', methods: ['GET'])]
    public function index(SuivisRepository $suivisRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $client = $this->getUser();

        $suivis = $paginator->paginate(
            $suivisRepository->findBy(['client' => $client], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('suivis/index.html.twig', [
            'suivis' => $suivis,
        ]);
    }

    #[Route('/add/{id}', name: 'suivre_new', methods: ['GET', 'POST'])]
    public function new(User $vendeur, Request $request, EntityManagerInterface $entityManager, SuivisRepository $suivisRepository): Response
    {
        $client = $this->getUser();

        if (!$vendeur) {
            dd('Impossible de suivre ce vendeur');
        }

        if (!$vendeur) return $this->json([
            'code' => 404,
            'message' => 'Vous devez être connecter!'
        ], 403);

        if ($vendeur->isFollowed($client)) {

            $suivi = $suivisRepository->findOneBy([
                'client' => $client,
                'vendeur' => $vendeur
            ]);

            $entityManager->remove($suivi);
            $entityManager->flush();
            /*return $this->json([
                'code' => 200,
                'message' => 'Vous ne suivez plus ce vendeur',
            ], 200);*/

        } else {
            $suivi = new Suivis;

            $suivi->setClient($client);
            $suivi->setVendeur($vendeur);
            $entityManager->persist($suivi);
            $entityManager->flush();
            /*return $this->json([
                'code' => 200,
                'message' => 'Vous suivez ce vendeur',
            ], 200);*/
        }

        return $this->redirectToRoute('suivis_index', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/{id}', name: 'suivis_delete', methods: ['POST'])]
    public function delete(Request $request, Suivis $suivi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$suivi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($suivi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('suivis_index', [], Response::HTTP_SEE_OTHER);
    }
}
