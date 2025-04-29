<?php

namespace App\Controller\Vendeur;

use App\Entity\Media;
use App\Form\Media1Type;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur/services/medias')]
class VendeurServicesMediasController extends AbstractController
{
    #[Route('/', name: 'app_vendeur_services_medias_index', methods: ['GET'])]
    public function index(MediaRepository $mediaRepository): Response
    {
        return $this->render('vendeur/vendeur_services_medias/index.html.twig', [
            'media' => $mediaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vendeur_services_medias_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $media = new Media();
        $form = $this->createForm(Media1Type::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($media);
            $entityManager->flush();

            return $this->redirectToRoute('app_vendeur_services_medias_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vendeur/vendeur_services_medias/new.html.twig', [
            'media' => $media,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vendeur_services_medias_show', methods: ['GET'])]
    public function show(Media $media): Response
    {
        return $this->render('vendeur/vendeur_services_medias/show.html.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vendeur_services_medias_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Media $media, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Media1Type::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vendeur_services_medias_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vendeur/vendeur_services_medias/edit.html.twig', [
            'media' => $media,
            'form' => $form,
        ]);
    }

    #[Route('/vendeur-delete/{id}', name: 'app_vendeur_services_medias_delete', methods: ['POST'])]
    public function vendeurDelete(Request $request, Media $media, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$media->getId(), $request->request->get('_token'))) {
            $service = $media->getMicroservice();
            $entityManager->remove($media);
            $entityManager->flush();

            $this->addFlash('success', "L'image a bien été supprimer de la galérie");
        }

        return $this->redirectToRoute('vendeur_microservices_galerie', [
            'id' => $service->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin-delete/{id}', name: 'app_admin_services_medias_delete', methods: ['POST'])]
    public function delete(Request $request, Media $media, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$media->getId(), $request->request->get('_token'))) {
            $service = $media->getMicroservice();
            $entityManager->remove($media);
            $entityManager->flush();

            $this->addFlash('success', "L'image a bien été supprimer de la galérie");
        }

        return $this->redirectToRoute('app_admin_services_galerie', [
            'id' => $service->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
