<?php

namespace App\Controller\Admin;

use App\Entity\Offre;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/offres')]
class AdminOffresController extends AbstractController
{
    private $sluger;

    public function __construct(SluggerInterface $sluger)
    {
        $this->sluger = $sluger;
    }

    #[Route('/', name: 'app_admin_offres_index', methods: ['GET'])]
    public function index(OffreRepository $offreRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $offres = $paginator->paginate(
            $offreRepository->findBy([], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/admin_offres/index.html.twig', [
            'offres' => $offres,
        ]);
    }

    #[Route('/nouveau-pack', name: 'app_admin_offres_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OffreRepository $offreRepository): Response
    {
        $offre = new Offre();
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offre->setSlug(strtolower($this->sluger->slug($form->get('name')->getData())));
            $offreRepository->save($offre, true);
            $this->addFlash('success', 'Le pack a bien été créer avec succès');
            return $this->redirectToRoute('app_admin_offres_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_offres/new.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_offres_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('admin/admin_offres/show.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_offres_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, OffreRepository $offreRepository): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Le pack a été mise a jour avec succès');
            $offre->setSlug(strtolower($this->sluger->slug($form->get('name')->getData())));
            $offreRepository->save($offre, true);

            return $this->redirectToRoute('app_admin_offres_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_offres/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_offres_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, OffreRepository $offreRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->request->get('_token'))) {
            $offreRepository->remove($offre, true);
            $this->addFlash('success', 'Le pack a bien été supprimé avec succès');
        }

        return $this->redirectToRoute('app_admin_offres_index', [], Response::HTTP_SEE_OTHER);
    }
}
