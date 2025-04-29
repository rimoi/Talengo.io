<?php

namespace App\Controller\Vendeur;

use App\Entity\EmploisTemps;
use App\Entity\User;
use App\Form\EmploisTempsType;
use App\Form\UserTempsType;
use App\Repository\EmploisTempsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/planning')]
class VendeurEmploisTempsController extends AbstractController
{
    #[Route('/', name: 'app_vendeur_emplois_temps_index', methods: ['GET', 'POST'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserTempsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
            
            $this->addFlash('success', 'Le contenu a bien été ajouté');
            return $this->redirectToRoute('app_vendeur_emplois_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vendeur/emplois_temps/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vendeur_emplois_temps_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmploisTemps $emploisTemp, EmploisTempsRepository $emploisTempsRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmploisTempsType::class, $emploisTemp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash('success', 'Le contenu a bien été mise à jour');
            return $this->redirectToRoute('app_vendeur_emplois_temps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vendeur/emplois_temps/edit.html.twig', [
            'emplois_temp' => $emploisTemp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_emplois_temps_delete', methods: ['POST'])]
    public function delete(Request $request, EmploisTemps $emploisTemp, EmploisTempsRepository $emploisTempsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emploisTemp->getId(), $request->request->get('_token'))) {
            
            $this->addFlash('success', 'Le contenu a bien été supprimer');
            $emploisTempsRepository->remove($emploisTemp, true);
        }

        return $this->redirectToRoute('app_vendeur_emplois_temps_index', [], Response::HTTP_SEE_OTHER);
    }

    public function getOrdre($jour){

        $ordre = 0;

        if ($jour == 'Lundi') {
            $ordre = 1;
        }elseif ($jour == 'Mardi') {
            $ordre = 2;
        }elseif ($jour == 'Mercredi') {
            $ordre = 3;
        }elseif ($jour == 'Jeudi') {
            $ordre = 4;
        }elseif ($jour == 'Vendredi') {
            $ordre = 5;
        }elseif ($jour == 'Samedi') {
            $ordre = 6;
        }elseif ($jour == 'Dimanche') {
            $ordre = 7;
        }

        return $ordre;
    }
}
