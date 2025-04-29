<?php

namespace App\Controller\Vendeur;

use App\Entity\Remboursement;
use App\Form\RemboursementType;
use App\Repository\RemboursementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/remboursements')]
class VendeurRemboursementsController extends AbstractController
{
    #[Route('/', name: 'app_vendeur_remboursements_index', methods: ['GET'])]
    public function index(RemboursementRepository $remboursementRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();

        $remboursements = $paginator->paginate(
            $remboursementRepository->findBy(['user' => $user], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('vendeur/remboursements/index.html.twig', [
            'remboursements' => $remboursements,
        ]);
    }

    #[Route('/new', name: 'app_vendeur_remboursements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RemboursementRepository $remboursementRepository): Response
    {
        $remboursement = new Remboursement();
        $form = $this->createForm(RemboursementType::class, $remboursement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $remboursement->setUser($this->getUser());
            $remboursement->setStatut('En attente');
            $remboursementRepository->save($remboursement, true);

            $this->addFlash('success', 'Votre demande a bien été envoyée');
            return $this->redirectToRoute('app_vendeur_remboursements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vendeur/remboursements/new.html.twig', [
            'remboursement' => $remboursement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_remboursements_show', methods: ['GET'])]
    public function show(Remboursement $remboursement): Response
    {
        return $this->render('vendeur/remboursements/show.html.twig', [
            'remboursement' => $remboursement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vendeur_remboursements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Remboursement $remboursement, RemboursementRepository $remboursementRepository): Response
    {
        $form = $this->createForm(RemboursementType::class, $remboursement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $remboursementRepository->save($remboursement, true);

            return $this->redirectToRoute('app_vendeur_remboursements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vendeur/remboursements/edit.html.twig', [
            'remboursement' => $remboursement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_remboursements_delete', methods: ['POST'])]
    public function delete(Request $request, Remboursement $remboursement, RemboursementRepository $remboursementRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$remboursement->getId(), $request->request->get('_token'))) {
            $remboursementRepository->remove($remboursement, true);
        }

        return $this->redirectToRoute('app_vendeur_remboursements_index', [], Response::HTTP_SEE_OTHER);
    }
}
