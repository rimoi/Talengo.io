<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Form\Commande2Type;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/commandes')]
class AdminCommandesController extends AbstractController
{
    #[Route('/', name: 'app_admin_commandes_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $commandes = $paginator->paginate(
            $commandeRepository->findBy([], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/admin_commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/new', name: 'app_admin_commandes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(Commande2Type::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_commandes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_commandes/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_commandes_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('admin/admin_commandes/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_commandes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Commande2Type::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_commandes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_commandes/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_commandes_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_commandes_index', [], Response::HTTP_SEE_OTHER);
    }
}
