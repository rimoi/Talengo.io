<?php

namespace App\Controller;

use App\Entity\Rapport;
use App\Form\RapportType;
use App\Repository\RapportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/rapports')]
class AdminRapportsController extends AbstractController
{
    #[Route('/', name: 'app_admin_rapports_index', methods: ['GET'])]
    public function index(RapportRepository $rapportRepository): Response
    {

        return $this->render('admin/admin_rapports/index.html.twig', [
            'rapports' => $rapportRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_rapports_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RapportRepository $rapportRepository): Response
    {
        $rapport = new Rapport();
        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rapportRepository->save($rapport, true);

            return $this->redirectToRoute('app_admin_rapports_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_rapports/new.html.twig', [
            'rapport' => $rapport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_rapports_show', methods: ['GET'])]
    public function show(Rapport $rapport): Response
    {
        return $this->render('admin/admin_rapports/show.html.twig', [
            'rapport' => $rapport,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_rapports_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rapport $rapport, RapportRepository $rapportRepository): Response
    {
        $form = $this->createForm(RapportType::class, $rapport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rapportRepository->save($rapport, true);

            return $this->redirectToRoute('app_admin_rapports_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_rapports/edit.html.twig', [
            'rapport' => $rapport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_rapports_delete', methods: ['POST'])]
    public function delete(Request $request, Rapport $rapport, RapportRepository $rapportRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rapport->getId(), $request->request->get('_token'))) {
            $rapportRepository->remove($rapport, true);
        }

        return $this->redirectToRoute('app_admin_rapports_index', [], Response::HTTP_SEE_OTHER);
    }
}
