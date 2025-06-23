<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Disponibilite;
use App\Entity\Microservice;
use App\Entity\SearchService;
use App\Entity\ServiceOption;
use App\Entity\ServiceSignale;
use App\Form\Commande2Type;
use App\Form\CommandeType;
use App\Form\SearchServiceType;
use App\Form\ServiceSignaleType;
use App\Repository\AvisRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\EmploisTempsRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\OffreRepository;
use App\Repository\ServiceOptionRepository;
use App\Repository\ServiceSignaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/microservices')]
class MicroserviceController extends AbstractController
{
    #[Route('/', name: 'microservices', methods: ['GET', 'POST'])]
    public function index(MicroserviceRepository $microserviceRepository, PaginatorInterface $paginator, Request $request, CategorieRepository $categorieRepository, OffreRepository $offreRepository): Response
    {
        $search = new SearchService();
        $search->page = $request->get('page', 1);
        $ville = isset($_COOKIE['LINKS-VILLE']) ? $_COOKIE['LINKS-VILLE'] : '';
        $search->setVille($ville);
        $form = $this->createForm(SearchServiceType::class, $search);
        $form->handleRequest($request);

        $microservices = $microserviceRepository->findSearch($search);
        $categories = $categorieRepository->findBy([], ['created' => 'DESC']);

        if ($request->get('ajax')) {

            return new JsonResponse([
                'content' => $this->renderView('microservice/composants/_listing.html.twig', ['microservices' => $microservices]),
                /*'sorting' => $this->renderView('microservice/composants/_sorting.html.twig', ['microservices' => $microservices]),*/
                'form' => $this->renderView('microservice/composants/_search_form.html.twig', ['microservices' => $microservices, 'form' => $form->createView()]),
                'pagination' => $this->renderView('microservice/composants/_pagination.html.twig', ['microservices' => $microservices]),
            ]);
        }

        return $this->renderForm('microservice/index.html.twig', [
            'microservices' => $microservices,
            'categories' => $categories,
            'form' => $form,
            'ville' => $ville,
            'query' => $search->q,
            'packs' => $offreRepository->findBy(['online' => 1], ['created' => 'DESC']),
        ]);
    }

    #[Route('/categories/{slug}', name: 'microservices_categories', methods: ['GET', 'POST'])]
    public function categories(MicroserviceRepository $microserviceRepository, PaginatorInterface $paginator, Request $request, CategorieRepository $categorieRepository, $slug): Response
    {
        $search = new SearchService();
        $search->page = $request->get('page', 1);
        $ville = isset($_COOKIE['LINKS-VILLE']) ? $_COOKIE['LINKS-VILLE'] : '';
//        $search->setVille($ville);
        $form = $this->createForm(SearchServiceType::class, $search);
        $form->handleRequest($request);

        $microservices = $microserviceRepository->findSearch($search);
        $categories = $categorieRepository->findBy([], ['created' => 'DESC']);

        if ($request->get('ajax')) {

            return new JsonResponse([
                'content' => $this->renderView('microservice/composants/_listing.html.twig', ['microservices' => $microservices]),
                /*'sorting' => $this->renderView('microservice/composants/_sorting.html.twig', ['microservices' => $microservices]),*/
                'form' => $this->renderView('microservice/composants/_search_form.html.twig', ['microservices' => $microservices, 'form' => $form->createView()]),
                'pagination' => $this->renderView('microservice/composants/_pagination.html.twig', ['microservices' => $microservices]),
            ]);
        }

        return $this->renderForm('microservice/categories.html.twig', [
            'microservices' => $microservices,
            'categories' => $categories,
            'ville' => $ville,
            'form' => $form,
            'categorie' => $categorieRepository->findOneBy(['slug' => $slug]),
        ]);
    }

    #[Route('/{slug}/online', name: 'microservice_online', methods: ['GET', 'POST'])]
    public function online(Microservice $microservice, Request $request, EntityManagerInterface $entityManager): Response
    {
        $microservice->setOnline(true);

        $entityManager->flush();

        $this->addFlash('success', sprintf('Le service `%s` a été publié avec succès.', $microservice->getName()));

        return $this->redirectToRoute('app_admin_services_index');
    }

    #[Route('/{slug}/offline', name: 'microservice_offline', methods: ['GET', 'POST'])]
    public function offline(Microservice $microservice, Request $request, EntityManagerInterface $entityManager): Response
    {
        $microservice->setOnline(false);

        $entityManager->flush();

        $this->addFlash('success', sprintf('Le service `%s` a été mis hors ligne avec succès.', $microservice->getName()));

        return $this->redirectToRoute('app_admin_services_index');
    }

    #[Route('/{slug}', name: 'microservice_details', methods: ['GET', 'POST'])]
    public function details(Microservice $microservice, Request $request, EntityManagerInterface $entityManager, MicroserviceRepository $microserviceRepository, AvisRepository $avisRepository, ServiceOptionRepository $serviceOptionRepository, ServiceSignaleRepository $serviceSignaleRepository, DisponibiliteRepository $disponibiliteRepository, CommandeRepository $commandeRepository): Response
    {
        $commandeFormType = Commande2Type::class;
        $isHiden = true;

        $commande = new Commande();
        $commandeForm = $this->createForm($commandeFormType, $commande);
        $commandeForm->handleRequest($request);


        if ($commandeForm->isSubmitted() && $commandeForm->isValid()) {

            if (is_null($this->getUser())) {
                return $this->redirectToRoute('app_login', ['redirect' => $microservice->getSlug()]);
            }


            if ($options = $request->get('options')) {
                $options = $entityManager->getRepository(ServiceOption::class)->findBy(['id' => $options]);

                foreach ($options as $option) {
                    $commande->addServiceOption($option);
                    $option->addCommande($commande);
                }
            }

            if ($request->get('total')) {
                $commande->setMontant($request->get('total'));
            }

            $commande->setReservationDate(new \DateTime());
            $commande->setMicroservice($microservice);
            $commande->setClient($this->getUser());
            $commande->setVendeur($microservice->getVendeur());
            $commande->setDestinataire($microservice->getVendeur());
            $commande->setConfirmationClient(false);
            $commande->setStatut('Non payée');
            $commande->setOffre('Réservation');
            $commande->setValidate(false);
            $commande->setDeliver(false);
            $commande->setCancel(false);
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('commander_microservice_reservation', [
                'slug' => $microservice->getSlug(),
                'commande' => $commande->getId(),
            ]);
        }

        return $this->render('microservice/details.html.twig', [
            'microservice' => $microservice,
            'commandeForm' => $commandeForm->createView(),
            'prix' => $microservice->getPrix(),
            'isHiden' => $isHiden,
            'all_comment' => $request->get('all_comment'),
            'all_service' => $request->get('all_service'),
        ]);
    }

}
