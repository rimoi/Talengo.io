<?php

namespace App\Controller\Vendeur;

use App\Entity\Competence;
use App\Form\CompetenceType;
use App\Repository\CompetenceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/skills')]
class VendeurCompetenceController extends AbstractController
{
    #[Route('/', name: 'app_vendeur_skill_index', methods: ['GET'])]
    public function index(CompetenceRepository $competenceRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();

        $realisations = $paginator->paginate(
            $competenceRepository->findBy(['user' => $user], ['createdAt' => 'DESC']),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('vendeur/competence/index.html.twig', [
            'realisations' => $realisations,
        ]);
    }

    #[Route('/new', name: 'app_vendeur_skill_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CompetenceRepository $realisationRepository): Response
    {
        $realisation = new Competence();
        $form = $this->createForm(CompetenceType::class, $realisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $realisation->setUser($this->getUser());
            $realisationRepository->save($realisation, true);
            $this->addFlash('success', 'Le contenu a bien été créé');
            return $this->redirectToRoute('app_vendeur_skill_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('success', 'le contenu a bien été enregistré');
        }
        
        return $this->renderForm('vendeur/competence/new.html.twig', [
            'realisation' => $realisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_skill_show', methods: ['GET'])]
    public function show(Competence $realisation): Response
    {
        $this->denyAccessUnlessGranted('realisation_edit', $realisation);

        return $this->render('vendeur/competence/show.html.twig', [
            'realisation' => $realisation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vendeur_skill_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Competence $realisation, CompetenceRepository $realisationRepository): Response
    {
        $this->denyAccessUnlessGranted('realisation_edit', $realisation);

        $form = $this->createForm(CompetenceType::class, $realisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $realisationRepository->save($realisation, true);

            return $this->redirectToRoute('app_vendeur_skill_index', [], Response::HTTP_SEE_OTHER);

            $this->addFlash('success', 'Le contenu a bien été mise à jour');
        }

        return $this->renderForm('vendeur/competence/edit.html.twig', [
            'realisation' => $realisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_skill_delete', methods: ['POST'])]
    public function delete(Request $request, Competence $realisation, CompetenceRepository $realisationRepository): Response
    {
        $this->denyAccessUnlessGranted('realisation_edit', $realisation);

        if ($this->isCsrfTokenValid('delete'.$realisation->getId(), $request->request->get('_token'))) {
            $realisationRepository->remove($realisation, true);
        }

        return $this->redirectToRoute('app_vendeur_skill_index', [], Response::HTTP_SEE_OTHER);
    }
}
