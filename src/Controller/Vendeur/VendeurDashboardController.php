<?php

namespace App\Controller\Vendeur;

use App\Entity\Portefeuille;
use App\Repository\CommandeRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\RemboursementRepository;
use App\Repository\RetraitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur')]
class VendeurDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'vendeur_dashboard')]
    public function index(EntityManagerInterface $manager, MicroserviceRepository $microserviceRepository, CommandeRepository $commandeRepository, RetraitRepository $retraitRepository, RemboursementRepository $remboursementRepository): Response
    {
        $user = $this->getUser();

        if(!$user->getPortefeuille()){
            
            $portefeuille = new Portefeuille();
            $portefeuille->setVendeur($user);
            $portefeuille->setSoldeDisponible(0);
            $portefeuille->setSoldeEnCours(0);

            $manager->persist($portefeuille);
            $manager->flush();
        }

        return $this->render('vendeur/dashboard/index.html.twig', [
            'services' => count($microserviceRepository->findBy(['vendeur' => $user])),
            'commandes' => count($commandeRepository->findBy(['vendeur' => $user, 'statut' => 'En attente'])),
            'retrait' => $retraitRepository->findTotal(['vendeur' => $user]),
            'retraits' => count($retraitRepository->findBy(['vendeur' => $user])),
            'remboursement' => $remboursementRepository->findTotal(['vendeur' => $user]),
            'remboursements' => count($remboursementRepository->findBy(['vendeur' => $user])),
        ]);
    }
}
