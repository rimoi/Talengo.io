<?php

namespace App\Controller\Vendeur;

use App\Entity\Portefeuille;
use App\Repository\PortefeuilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/portefeuille')]
class VendeurPortefeuilleController extends AbstractController
{
    #[Route('/', name: 'vendeur_portefeuille')]
    public function index(PortefeuilleRepository $portefeuilleRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $portefeuille = $portefeuilleRepository->findOneBy(['vendeur' => $user]);

        if (!$portefeuille) {

            $portefeuille = new Portefeuille();
            $portefeuille->setSoldeDisponible(0);
            $portefeuille->setSoldeEnCours(0);
            $portefeuille->setVendeur($user);
            $entityManager->persist($portefeuille);
            $entityManager->flush();

        }

        return $this->render('vendeur/portefeuille/index.html.twig', [
            'portefeuille' => $portefeuille,
        ]);
    }
}
