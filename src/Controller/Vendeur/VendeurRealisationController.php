<?php

namespace App\Controller\Vendeur;

use App\Entity\Realisation;
use App\Form\PortfolioType;
use App\Form\RealisationType;
use App\Repository\RealisationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/autres-realisations')]
class VendeurRealisationController extends AbstractController
{
    #[Route('/', name: 'app_vendeur_realisation_index', methods: ['GET'])]
    public function index(RealisationRepository $realisationRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();

        $realisations = $paginator->paginate(
            $realisationRepository->findBy(['vendeur' => $user], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('vendeur/realisations/index.html.twig', [
            'realisations' => $realisations,
        ]);
    }

    #[Route('/new', name: 'app_vendeur_realisation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RealisationRepository $realisationRepository): Response
    {
        $realisation = new Realisation();
        $form = $this->createForm(PortfolioType::class, $realisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $realisation->setVendeur($this->getUser());
            $realisationRepository->save($realisation, true);
            $this->addFlash('success', 'Le contenu a bien été créé');
            return $this->redirectToRoute('app_vendeur_realisation_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('success', 'le contenu a bien été enregistré');
        }
        
        return $this->renderForm('vendeur/realisations/new.html.twig', [
            'realisation' => $realisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_realisation_show', methods: ['GET'])]
    public function show(Realisation $realisation): Response
    {
        $this->denyAccessUnlessGranted('realisation_edit', $realisation);

        return $this->render('vendeur/realisations/show.html.twig', [
            'realisation' => $realisation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vendeur_realisation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Realisation $realisation, RealisationRepository $realisationRepository): Response
    {
        $this->denyAccessUnlessGranted('realisation_edit', $realisation);

        $form = $this->createForm(PortfolioType::class, $realisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $realisationRepository->save($realisation, true);

            return $this->redirectToRoute('app_vendeur_realisation_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('success', 'Le contenu a bien été mise à jour');
        }

        return $this->renderForm('vendeur/realisations/edit.html.twig', [
            'realisation' => $realisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_realisation_delete', methods: ['POST'])]
    public function delete(Request $request, Realisation $realisation, RealisationRepository $realisationRepository): Response
    {
        $this->denyAccessUnlessGranted('realisation_edit', $realisation);

        if ($this->isCsrfTokenValid('delete'.$realisation->getId(), $request->request->get('_token'))) {
            $realisationRepository->remove($realisation, true);
        }

        return $this->redirectToRoute('app_vendeur_realisation_index', [], Response::HTTP_SEE_OTHER);
    }
}
