<?php

namespace App\Controller\Vendeur;

use App\Entity\Retrait;
use App\Form\RetraitType;
use App\Repository\RetraitRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/retraits')]
class VendeurRetraitsController extends AbstractController
{
    #[Route('/', name: 'app_vendeur_retraits_index', methods: ['GET'])]
    public function index(RetraitRepository $retraitRepository): Response
    {
        $vendeur = $this->getUser();
        
        return $this->render('vendeur/retraits/index.html.twig', [
            'retraits' => $retraitRepository->findBy(['vendeur' => $vendeur]),
            'retraitEnattente' => $retraitRepository->findBy(['vendeur' => $vendeur, 'statut' => 'En attente'])
        ]);
    }

    #[Route('/nouveau-retrait', name: 'app_vendeur_retraits_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, RetraitRepository $retraitRepository, MailerService $mailer): Response
    {
        $retrait = new Retrait();
        $form = $this->createForm(RetraitType::class, $retrait);
        $form->handleRequest($request);
        $vendeur = $this->getUser();

        $retraitsenattente = $retraitRepository->findBy(['vendeur' => $vendeur, 'statut' => 'En attente']);

        if ($form->isSubmitted() && $form->isValid()) {

            $redirectUrl = $this->redirectToRoute('app_vendeur_retraits_index', [], Response::HTTP_SEE_OTHER);
            $montant = $form->get('montant')->getData();
            $soldeVendeur = $vendeur->getPortefeuille()->getSoldeDisponible();

            if ($montant > $soldeVendeur) {
                $this->addFlash('danger', "Votre demande n'a pas été envoyée, votre solde est insuffisant $soldeVendeur €, pour effectuer un retrait de fond de $montant €.");
                return $this->redirectToRoute('app_vendeur_retraits_new', [], Response::HTTP_SEE_OTHER);
            }

            $retrait->setVendeur($vendeur);
            $retrait->setStatut('En attente');
            $entityManager->persist($retrait);
            $entityManager->flush();
            
            /** Envoie du mail au vendeur */
            $mailer->sendDemandeMail(
                'sidilekhalifa1@gmail.com',
                $retrait->getVendeur()->getEmail(),
                'MISSION C2C - Nouveau retrait',
                'mails/_retrait.html.twig',
                $retrait->getVendeur(),
                $retrait
            );
            
            /** Envoie du mail à l'auteur */
            $mailer->sendDemandeMail(
                'sidilekhalifa1@gmail.com',
                'sidilekhalifa1@gmail.com',
                'MISSION C2C - Nouveau retrait',
                'mails/_retrait.html.twig',
                $retrait->getVendeur(),
                $retrait
            );

            $this->addFlash('success', 'Votre demande a bien été envoyée');
            return $redirectUrl;
        }

        return $this->renderForm('vendeur/retraits/new.html.twig', [
            'retrait' => $retrait,
            'form' => $form,
            'retraitEnattente' => $retraitsenattente,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_retraits_show', methods: ['GET'])]
    public function show(Retrait $retrait): Response
    {
        return $this->render('vendeur/retraits/show.html.twig', [
            'retrait' => $retrait,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vendeur_retraits_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Retrait $retrait, RetraitRepository $retraitRepository): Response
    {
        $form = $this->createForm(RetraitType::class, $retrait);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $retraitRepository->save($retrait, true);

            return $this->redirectToRoute('app_vendeur_retraits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vendeur/retraits/edit.html.twig', [
            'retrait' => $retrait,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_retraits_delete', methods: ['POST'])]
    public function delete(Request $request, Retrait $retrait, RetraitRepository $retraitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$retrait->getId(), $request->request->get('_token'))) {
            $retraitRepository->remove($retrait, true);
        }

        return $this->redirectToRoute('app_vendeur_retraits_index', [], Response::HTTP_SEE_OTHER);
    }
}
