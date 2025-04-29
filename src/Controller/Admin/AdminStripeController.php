<?php

namespace App\Controller\Admin;

use App\Entity\Stripe;
use App\Form\StripeType;
use App\Repository\StripeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/stripe')]
class AdminStripeController extends AbstractController
{
    #[Route('/', name: 'app_admin_stripe_index', methods: ['GET'])]
    public function index(StripeRepository $stripeRepository): Response
    {
        return $this->render('admin/admin_stripe/index.html.twig', [
            'stripes' => $stripeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_stripe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stripe = new Stripe();
        $form = $this->createForm(StripeType::class, $stripe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stripe);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_stripe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_stripe/new.html.twig', [
            'stripe' => $stripe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_stripe_show', methods: ['GET'])]
    public function show(Stripe $stripe): Response
    {
        return $this->render('admin/admin_stripe/show.html.twig', [
            'stripe' => $stripe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_stripe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stripe $stripe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StripeType::class, $stripe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_stripe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_stripe/edit.html.twig', [
            'stripe' => $stripe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_stripe_delete', methods: ['POST'])]
    public function delete(Request $request, Stripe $stripe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stripe->getId(), $request->request->get('_token'))) {
            $entityManager->remove($stripe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_stripe_index', [], Response::HTTP_SEE_OTHER);
    }
}
