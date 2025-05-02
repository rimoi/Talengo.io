<?php

namespace App\Controller\Admin;

use App\Entity\Retrait;
use App\Form\Retrait1Type;
use App\Repository\RetraitRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/retraits')]
class AdminRetraitsController extends AbstractController
{
    #[Route('/', name: 'app_admin_retraits_index', methods: ['GET'])]
    public function index(RetraitRepository $retraitRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $retraits = $paginator->paginate(
            $retraitRepository->findBy([], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/admin_retraits/index.html.twig', [
            'retraits' => $retraits,
        ]);
    }

    #[Route('/new', name: 'app_admin_retraits_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RetraitRepository $retraitRepository): Response
    {
        $retrait = new Retrait();
        $form = $this->createForm(Retrait1Type::class, $retrait);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $retraitRepository->save($retrait, true);

            return $this->redirectToRoute('app_admin_retraits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_retraits/new.html.twig', [
            'retrait' => $retrait,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_retraits_show', methods: ['GET'])]
    public function show(Retrait $retrait): Response
    {
        return $this->render('admin/admin_retraits/show.html.twig', [
            'retrait' => $retrait,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_retraits_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Retrait $retrait, RetraitRepository $retraitRepository, MailerService $mailer): Response
    {
        $form = $this->createForm(Retrait1Type::class, $retrait);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $retraitRepository->save($retrait, true);

            /** Envoie de mail au vendeur */
            $mailer->sendDemandeMail(
                'sidilekhalifa1@gmail.com',
                $retrait->getVendeur()->getEmail(),
                'Talengo.io - retrait rejeté',
                'mails/_retrait_rejeter.html.twig',
                $retrait->getVendeur(),
                $retrait,
                $retrait
            );

            $this->addFlash('success', 'le contenu a bien été enregistré');

            return $this->redirectToRoute('app_admin_retraits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_retraits/edit.html.twig', [
            'retrait' => $retrait,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_retraits_delete', methods: ['POST'])]
    public function delete(Request $request, Retrait $retrait, RetraitRepository $retraitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$retrait->getId(), $request->request->get('_token'))) {
            $retraitRepository->remove($retrait, true);

            $this->addFlash('success', 'La demande à bien été supprimée');
        }

        return $this->redirectToRoute('app_admin_retraits_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/valider/{id}', name: 'app_admin_retraits_valider', methods: ['POST'])]
    public function valider(Request $request, Retrait $retrait, EntityManagerInterface $entityManager, MailerService $mailer): Response
    {
        if ($this->isCsrfTokenValid('valider'.$retrait->getId(), $request->request->get('_token'))) {
            
            /** Soustraction des revenus du vendeur suite à un retrait */
            $portfeuille = $retrait->getVendeur()->getPortefeuille();
            $portfeuille->setSoldeDisponible($portfeuille->getSoldeDisponible() - $retrait->getMontant());
            $retrait->setStatut('Valider');
            $entityManager->flush();

            /** Envoie de mail au vendeur */
            $mailer->sendDemandeMail(
                'sidilekhalifa1@gmail.com',
                $retrait->getVendeur()->getEmail(),
                'Talengo.io - retrait validée',
                'mails/_retrait_valider.html.twig',
                $retrait->getVendeur(),
                $retrait,
                $retrait
            );

            $this->addFlash('success', 'La demande à bien été validée');
        }

        return $this->redirectToRoute('app_admin_retraits_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/rejeter/{id}', name: 'app_admin_retraits_rejeter', methods: ['POST'])]
    public function rejeter(Request $request, Retrait $retrait, EntityManagerInterface $entityManager, MailerService $mailer): Response
    {
        if ($this->isCsrfTokenValid('rejeter'.$retrait->getId(), $request->request->get('_token'))) {
            $retrait->setStatut('Rejeter');
            $entityManager->flush();
            $this->addFlash('success', "La demande à bien été rejetée, indiquez une raison si possible afin d'informer le vendeur");
        }

        return $this->redirectToRoute('app_admin_retraits_edit', ['id' => $retrait->getId()], Response::HTTP_SEE_OTHER);
    }
}
