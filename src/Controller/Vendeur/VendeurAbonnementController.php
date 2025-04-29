<?php

namespace App\Controller\Vendeur;

use App\Repository\AbonnementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/abonnement')]
class VendeurAbonnementController extends AbstractController
{
    #[Route('/', name: 'vendeur_abonnement')]
    public function abonnement(AbonnementRepository $abonnementRepository): Response
    {
        $abonnement = $abonnementRepository->findOneBy(['user' => $this->getUser()]);

        if (!$abonnement) {
            return $this->redirectToRoute('stripe_abonnement_services');
        }

        return $this->render('vendeur/abonnement/index.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }
}
