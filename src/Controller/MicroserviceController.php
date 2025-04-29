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

        $similaires = $microserviceRepository->findBy(['vendeur' => $this->getUser()], ['created' => 'DESC'], 12);
        /** @var ServiceOption $option */
        $options = $microservice->getServiceOptions();
        $categories = ['Ingénieur son', 'Studio'];
        $isHiden = null;
        $tauxHoraire = '';
        $jours = [];

        $erreurmessage = '';
        $indisponible = '';

        $serviceSignale = new ServiceSignale();
        $servicesignaleForm = $this->createForm(ServiceSignaleType::class, $serviceSignale);
        $servicesignaleForm->handleRequest($request);

        if ($servicesignaleForm->isSubmitted() && $servicesignaleForm->isValid()) {

            $serviceSignale->setUser($this->getUser());
            $serviceSignale->setMicroservice($microservice);
            $serviceSignaleRepository->save($serviceSignale, true);
            $this->addFlash('success', 'Le service à bien été signalé merci pour votre collaboration');

            return $this->redirectToRoute('microservice_details', [
                'slug' => $microservice->getSlug()
            ], Response::HTTP_SEE_OTHER);

            $this->addFlash('success', 'le contenu a bien été enregistré');
        }

        if (in_array($microservice->getCategorie(), $categories)) {

            $commandeFormType = CommandeType::class;
        } else {
            $commandeFormType = Commande2Type::class;
            $isHiden = true;
        }

        $commande = new Commande();
        $commandeForm = $this->createForm($commandeFormType, $commande);
        $commandeForm->handleRequest($request);

        $categories = ['Ingenieur son', 'Studio'];

        if ($commandeForm->isSubmitted() && $commandeForm->isValid()) {

            if (is_null($this->getUser())) {
                return $this->redirectToRoute('app_login', ['redirect' => $microservice->getSlug()]);
            }

            $dateReservation = $commandeForm->get('reservationDate')->getData();

            $jour = $this->getJour(date_format($dateReservation, 'D'));
            /** @var Disponibilite */
            $disponibilites = $disponibiliteRepository->findBy(['service' => $microservice]);
            $jourAutorises = [];
            $indisponible = "";

            $jour = $this->getJour(date_format($dateReservation, 'D'));

            if (!empty($disponibilites)) {

                foreach ($disponibilites as $dispo) {
                    foreach ($dispo->getJours() as $value) {
                        $jourAutorises[] = $value;
                    }
                }

                if (!in_array($jour, $jourAutorises)) {
                    $indisponible = "Il n'y a aucune disponibilité pour ce prestataire $jour";
                    $jours = $jourAutorises;

                    $this->addFlash('danger', $indisponible);
                }

                //dd($jourAutorises);
            }

            $tauxHoraire = 24;

            /** Traitement pour cette catégorie */
            if (in_array($microservice->getCategorie(), $categories)) {

                $startHoure = $commandeForm->get('startHoure')->getData();
                $endHoure = $commandeForm->get('endHoure')->getData();

                if ($startHoure > $endHoure) {
                    $tauxHoraire = $startHoure - $endHoure;
                } elseif ($endHoure > $startHoure) {
                    $tauxHoraire = $endHoure - $startHoure;
                }

                $reservationStart = $startHoure;
                $reservationEnd = $endHoure;

                /** @var Commande $reservation Verification de l"existatnce de la resvation */
                $reservation = $commandeRepository->findOneBy([
                    'reservationDate' => $dateReservation,
                    'vendeur' => $microservice->getVendeur()
                ]);

                if ($reservation) {

                    $newReservationStart = $startHoure;
                    $heureDebutReservee = $reservation->getStartHoure();

                    if ($newReservationStart == $heureDebutReservee) {

                        $date = date_format($dateReservation, 'd/m/Y');

                        /** Injection de l'erreur */
                        $erreurmessage = "Ce prestataire a déjà une reservation ce $date à partir de $heureDebutReservee" . 'h';

                        //dd("Ce prestataire a déjà une reservation ce $date à partir de $heureDebutReservee" . 'h');
                    }
                }

                if (in_array($microservice->getCategorie(), $categories) && $tauxHoraire < 2) {
                    $erreurmessage = "Le taux horaire minimal pour les studios et ingenieurs de son est de 2heure, de $reservationStart h à $reservationEnd h, fait $tauxHoraire heure";
                }
            }

            /** S'il y a une reservation relative, on notifi l'utilisateur */
            if ($erreurmessage) {
                $this->addFlash('warning', $erreurmessage);
            } elseif ($indisponible) {
                $this->addFlash('warning', $indisponible);
            } else {

                $commande->setMicroservice($microservice);
                $commande->setTauxHoraire($tauxHoraire);
                $commande->setClient($this->getUser());
                $commande->setVendeur($microservice->getVendeur());
                $commande->setDestinataire($microservice->getVendeur());
                $commande->setConfirmationClient(false);
                $commande->setLu(false);
                $commande->setStatut('Non payée');
                $commande->setOffre('Reservation');
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
        }

        return $this->render('microservice/details.html.twig', [
            'microservice' => $microservice,
            'similaires' => $similaires,
            'servicesignaleForm' => $servicesignaleForm->createView(),
            'commandeForm' => $commandeForm->createView(),
            'prix' => $microservice->getPrix(),
            'options' => $options,
            'isHiden' => $isHiden,
            'erreurmessage' => $erreurmessage,
            'indisponible' => $indisponible,
            'jours' => $jours,
            'avisPositifs' => $avisRepository->findOneBy(['microservice' => $microservice, 'type' => 'Positif']),
            'disponibilites' => $disponibiliteRepository->findBy(['service' => $microservice], ['created' => 'ASC']),
        ]);
    }

    public function getJour($date)
    {
        $resultat = null;

        switch ($date) {
            case "Mon":
                $resultat = "Lundi";
                break;
            case "Tue":
                $resultat = "Mardi";
                break;
            case "Wed":
                $resultat = "Mercredi";
                break;
            case "Thu":
                $resultat = "Jeudi";
                break;
            case "Fri":
                $resultat = "Vendredi";
                break;
            case "Sat":
                $resultat = "Samedi";
                break;
            case "Sun":
                $resultat = "Dimanche";
                break;
            default:
                # code...
                break;
        }

        return $resultat;
    }
}
