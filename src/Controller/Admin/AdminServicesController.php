<?php

namespace App\Controller\Admin;

use App\Entity\Disponibilite;
use App\Entity\Media;
use App\Entity\Microservice;
use App\Entity\SearchService;
use App\Form\AdminSearchServiceType;
use App\Form\MediaType;
use App\Form\Microservice\AdminMicroserviceTitreType;
use App\Form\Microservice\DescriptionType;
use App\Form\Microservice\DisponibiliteType;
use App\Form\Microservice\IngenieurSonType;
use App\Form\Microservice\MicroserrviceOptionType;
use App\Form\Microservice\MicroserviceDisponibiliteType;
use App\Form\Microservice\MicroserviceGalerieType;
use App\Form\Microservice\MicroservicePublierType;
use App\Form\Microservice\MicroserviceTitreType;
use App\Form\MicroserviceType;
use App\Form\SearchServiceType;
use App\Repository\DisponibiliteRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\SuivisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/services')]
class AdminServicesController extends AbstractController
{
    private $sluger;

    public function __construct(SluggerInterface $sluger)
    {
        $this->sluger = $sluger;
    }

    #[Route('/', name: 'app_admin_services_index', methods: ['GET'])]
    public function index(MicroserviceRepository $microserviceRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $search = new SearchService();
        $search->page = $request->get('page', 1);

        $form = $this->createForm(AdminSearchServiceType::class, $search);
        $form->handleRequest($request);

        $microservices = $microserviceRepository->findAdminSearch($search);

        return $this->render('admin/admin_services/index.html.twig', [
            'microservices' => $microservices,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/nouveau-service', name: 'app_admin_services_new', methods: ['GET', 'POST'])]
    public function nouveau(Request $request, EntityManagerInterface $entityManager, SuivisRepository $suivisRepository): Response
    {
        $microservice = new Microservice();
        $form = $this->createForm(AdminMicroserviceTitreType::class, $microservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = $microservice->getName() . '-' . $microservice->getId();

            $microservice->setSlug(strtolower($this->sluger->slug($slug)));
            $microservice->setPrixMastering(0);
            $microservice->setPrixMixage(0);
            $microservice->setPrixBeatmaking(0);
            $microservice->setPrixComposition(0);
            $microservice->setPrix(0);
            $microservice->setPromo(false);
            $microservice->setOffline(false);
            $entityManager->persist($microservice);
            $entityManager->flush();

            $this->addFlash('success', 'Le contenu a bien été créé');

            return $this->redirectToRoute('app_admin_services_description', [
                'id' => $microservice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_services/titre.html.twig', [
            'microservice' => $microservice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/titre', name: 'app_admin_services_titre', methods: ['GET', 'POST'])]
    public function titre(Request $request, EntityManagerInterface $entityManager, Microservice $microservice): Response
    {
        $form = $this->createForm(AdminMicroserviceTitreType::class, $microservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = $form->get('name')->getData() . '-' . $microservice->getId();

            $microservice->setSlug(strtolower($this->sluger->slug($slug)));

            $entityManager->persist($microservice);
            $entityManager->flush();

            $this->addFlash('success', 'Le contenu a bien été créé');

            return $this->redirectToRoute('app_admin_services_description', [
                'id' => $microservice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_services/titre.html.twig', [
            'microservice' => $microservice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/description', name: 'app_admin_services_description', methods: ['GET', 'POST'])]
    public function description(Request $request, EntityManagerInterface $entityManager, Microservice $microservice): Response
    {
        $formType = DescriptionType::class;

        if ($microservice->getCategorie()->getSlug() == 'ingenieur-son') {

            $formType = IngenieurSonType::class;

        } else {
            $formType = DescriptionType::class;
        }

        $form = $this->createForm($formType, $microservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($microservice);
            $entityManager->flush();

            $this->addFlash('success', 'Le contenu a bien été créé');

            return $this->redirectToRoute('app_admin_services_options', [
                'id' => $microservice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_services/description.html.twig', [
            'microservice' => $microservice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/options', name: 'app_admin_services_options', methods: ['GET', 'POST'])]
    public function options(Request $request, EntityManagerInterface $entityManager, Microservice $microservice): Response
    {
        $form = $this->createForm(MicroserrviceOptionType::class, $microservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($microservice);
            $entityManager->flush();

            $this->addFlash('success', 'le contenu a bien été enregistré');

            return $this->redirectToRoute('app_admin_services_galerie', [
                'id' => $microservice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_services/options.html.twig', [
            'microservice' => $microservice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/galerie', name: 'app_admin_services_galerie', methods: ['GET', 'POST'])]
    public function galeries(Request $request, EntityManagerInterface $entityManager, Microservice $microservice): Response
    {
        $form = $this->createForm(MicroserviceGalerieType::class, $microservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($microservice);
            $entityManager->flush();

            $this->addFlash('success', "L'image a bien été téléchargée");

            return $this->redirectToRoute('app_admin_services_disponibilite', [
                'id' => $microservice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_services/galerie.html.twig', [
            'microservice' => $microservice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/disponibilite', name: 'app_admin_services_disponibilite', methods: ['GET', 'POST'])]
    public function planning(Request $request, EntityManagerInterface $entityManager, Microservice $microservice, DisponibiliteRepository $disponibiliteRepository): Response
    {        
        $form = $this->createForm(MicroserviceDisponibiliteType::class, $microservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->persist($microservice);
            $entityManager->flush();

            $this->addFlash('success', 'le contenu a bien été enregistré');

            return $this->redirectToRoute('app_admin_services_publication', [
                'id' => $microservice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_services/disponibilite.html.twig', [
            'microservice' => $microservice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/publication', name: 'app_admin_services_publication', methods: ['GET', 'POST'])]
    public function publication(Request $request, EntityManagerInterface $entityManager, Microservice $microservice): Response
    {
        $form = $this->createForm(MicroservicePublierType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $microservice->setOnline(true);
            $entityManager->persist($microservice);
            $entityManager->flush();

            $this->addFlash('success', 'Le contenu a bien été créé');

            return $this->redirectToRoute('app_admin_services_index', [
                'id' => $microservice->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_services/publication.html.twig', [
            'microservice' => $microservice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'app_admin_services_show', methods: ['GET'])]
    public function show(Microservice $microservice): Response
    {
        return $this->render('admin/admin_services/show.html.twig', [
            'microservice' => $microservice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_services_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Microservice $microservice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MicroserviceType::class, $microservice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_services/edit.html.twig', [
            'microservice' => $microservice,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_services_delete', methods: ['POST'])]
    public function delete(Request $request, Microservice $microservice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $microservice->getId(), $request->request->get('_token'))) {
            $entityManager->remove($microservice);
            $entityManager->flush();
            $this->addFlash('success', 'Le contenu a bien été supprimé.');
        }

        return $this->redirectToRoute('app_admin_services_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/service-disponibilite-delete/{id}', name: 'app_admin_services_disponibilite_delete', methods: ['POST'])]
    public function deleteDisponibilite(Request $request, Disponibilite $disponibilite, 
    EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$disponibilite->getId(), $request->request->get('_token'))) {
            $service = $disponibilite->getService();
            $entityManager->remove($disponibilite);
            $entityManager->flush();

            $this->addFlash('success', "La disponibilité pour ce service a bien été supprimer");
        }

        return $this->redirectToRoute('app_admin_services_disponibilite', [
            'id' => $service->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    public function getOrdre($jour){

        $ordre = 0;

        if ($jour == 'Lundi') {
            $ordre = 1;
        }elseif ($jour == 'Mardi') {
            $ordre = 2;
        }elseif ($jour == 'Mercredi') {
            $ordre = 3;
        }elseif ($jour == 'Jeudi') {
            $ordre = 4;
        }elseif ($jour == 'Vendredi') {
            $ordre = 5;
        }elseif ($jour == 'Samedi') {
            $ordre = 6;
        }elseif ($jour == 'Dimanche') {
            $ordre = 7;
        }

        return $ordre;
    }
}
