<?php

namespace App\Controller\Admin;

use App\Entity\Remboursement;
use App\Form\Remboursement1Type;
use App\Repository\RemboursementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/remboursements')]
class AdminRemboursementsController extends AbstractController
{
    #[Route('/', name: 'app_admin_remboursements_index', methods: ['GET'])]
    public function index(RemboursementRepository $remboursementRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $remboursements = $paginator->paginate(
            $remboursementRepository->findBy([], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/admin_remboursements/index.html.twig', [
            'remboursements' => $remboursements,
        ]);
    }

    #[Route('/new', name: 'app_admin_remboursements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RemboursementRepository $remboursementRepository): Response
    {
        $remboursement = new Remboursement();
        $form = $this->createForm(Remboursement1Type::class, $remboursement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $remboursementRepository->save($remboursement, true);

            return $this->redirectToRoute('app_admin_remboursements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_remboursements/new.html.twig', [
            'remboursement' => $remboursement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_remboursements_show', methods: ['GET'])]
    public function show(Remboursement $remboursement): Response
    {
        return $this->render('admin/admin_remboursements/show.html.twig', [
            'remboursement' => $remboursement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_remboursements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Remboursement $remboursement, RemboursementRepository $remboursementRepository): Response
    {
        $form = $this->createForm(Remboursement1Type::class, $remboursement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $remboursementRepository->save($remboursement, true);

            return $this->redirectToRoute('app_admin_remboursements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_remboursements/edit.html.twig', [
            'remboursement' => $remboursement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_remboursements_delete', methods: ['POST'])]
    public function delete(Request $request, Remboursement $remboursement, RemboursementRepository $remboursementRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$remboursement->getId(), $request->request->get('_token'))) {
            $remboursementRepository->remove($remboursement, true);
        }

        return $this->redirectToRoute('app_admin_remboursements_index', [], Response::HTTP_SEE_OTHER);
    }
}
