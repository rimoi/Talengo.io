<?php

namespace App\Controller\Vendeur;

use App\Entity\Commande;
use App\Entity\User;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/commandes')]
class VendeurCommandesController extends AbstractController
{
    #[Route('/', name: 'vendeur_commandes_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser();
        if (!$currentUser->isVerified()) {
            return $this->redirectToRoute('user_dashboard');
        }

        $commandes = $paginator->paginate(
            $commandeRepository->findBy(['vendeur' => $currentUser], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('vendeur/commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('details/{id}', name: 'vendeur_commandes_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('vendeur/commandes/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}', name: 'vendeur_commandes_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();

            $this->addFlash('success', 'La commande a bient été supprimée');
        }

        return $this->redirectToRoute('vendeur_commandes_index', [], Response::HTTP_SEE_OTHER);
    }
}
