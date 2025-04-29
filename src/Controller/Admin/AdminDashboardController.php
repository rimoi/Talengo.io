<?php

namespace App\Controller\Admin;

use App\Repository\AbonnementRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\PortefeuilleRepository;
use App\Repository\StripeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/tableau-de-bord')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(CategorieRepository $categorieRepository, MicroserviceRepository $microserviceRepository, UserRepository $userRepository, CommandeRepository $commandeRepository, StripeRepository $stripeRepository, AbonnementRepository $abonnementRepository, PortefeuilleRepository $portefeuilleRepository): Response
    {
        $categories = $categorieRepository->findAll();
        $stripes = $stripeRepository->findAll();

        // Démontage categorie
        $categorieName = [];
        $categorieColor = [];
        $categorieCount = [];

        // Démoncategoriee des données
        foreach($categories as $categorie){
            
            $categorieName[] = $categorie->getName();
            $categorieColor[] = $categorie->getHexColor();
            $categorieCount[] = count($categorie->getMicroservices());
        }

        // Démontage stripe
        $stripeName = [];
        $stripeColor = [];
        $stripeCount = [];

        // Démonstripee des données
        foreach($stripes as $stripe){
            
            $stripeName[] = $stripe->getName();
            $stripeColor[] = $stripe->getHexColor();
            $stripeCount[] = count($stripe->getAbonnements());
        }

        return $this->render('admin/admin_dashboard/index.html.twig', [
            'categorieNames' =>  json_encode($categorieName),
            'categorieColors' => json_encode($categorieColor),
            'categorieCount'    =>  json_encode($categorieCount),
            
            'stripeNames' =>  json_encode($stripeName),
            'stripeColors' => json_encode($stripeColor),
            'stripeCount'    =>  json_encode($stripeCount),

            'clients'    => $userRepository->findByRole('ROLE_CLIENT'),
            'vendeurs'    => $userRepository->findByRole('ROLE_VENDEUR'),
            'commandes'    => $commandeRepository->findAll(),
            'microservices'    => $microserviceRepository->findAll(),
            'abonnees'    => $abonnementRepository->findAll(),
            'offres'    => $stripes,
            'slodeDisponible'    => $portefeuilleRepository->findTotalSoldeDisponioble(),
            'slodeEncours'    => $portefeuilleRepository->findTotalSoldeEnCours(),
        ]);
    }
}
