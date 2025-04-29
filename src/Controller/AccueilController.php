<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\SearchService;
use App\Form\HomeServiceType;
use App\Helper\ArrayHelper;
use App\Repository\MicroserviceRepository;
use App\Repository\OffreRepository;
use App\Repository\UserRepository;
use App\Service\HomePageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'accueil', methods: ['POST', 'GET'])]
    public function index(
        Request $request,
        MicroserviceRepository $microserviceRepository,
        UserRepository $userRepository,
        OffreRepository $offreRepository,
        EntityManagerInterface $entityManager,
        HomePageService $homePageService
    ): Response
    {
        $categories = $entityManager->getRepository(Categorie::class)->findBy([], ['position' => 'ASC'], 6);

        $categories = ArrayHelper::sortBy($categories, 'position');

        foreach ($categories as $category) {
            $servicesBloc2[$category->getId()] = [];
        }

        $prestataires = $userRepository->findBy(['compte' => 'vendeur'], ['id' => 'DESC'], 6);

        $searchService = new SearchService();
        $form = $this->createForm(HomeServiceType::class, $searchService);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $ville = $searchService->getVille();
            setcookie('LINKS-VILLE', $ville, time()+31556926 , "/", "",  0);
            if (trim($ville)) {
                setcookie('LINKS-VILLE', $ville, time()+31556926 , "/", "",  0);

                $services = $microserviceRepository->findBylocation($ville);

                [$services, $servicesBloc2] = $homePageService->getMicroService($services);

                $prestataires = $userRepository->searchSeller($ville);
            } else  {

                unset($_COOKIE['LINKS-VILLE']);

                $microservices = $microserviceRepository->findBy(['online' => true, 'categorie' => $categories], ['id' => 'ASC'], 50);

                [$services, $servicesBloc2] = $homePageService->getMicroService($microservices);
            }

        } elseif ( $_COOKIE['LINKS-VILLE'] ?? false ) {
            $ville = $_COOKIE['LINKS-VILLE'];
            $searchService->setQ($ville);

            $services = $microserviceRepository->findBylocation($ville);

            [$services, $servicesBloc2] = $homePageService->getMicroService($services);

            $prestataires = $userRepository->searchSeller($ville);

        } else {
            $microservices = $microserviceRepository->findBy(['online' => true, 'categorie' => $categories], ['id' => 'ASC'], 50);

            [$services, $servicesBloc2] = $homePageService->getMicroService($microservices);
        }

        return $this->render('accueil/index.html.twig', [
            'microservices' => $services,
            'vendeurs' => $prestataires,
            'servicesBloc2' => $servicesBloc2,
            'form' => $form->createView(),
            'ville' => $searchService->getVille(),
            'packs' => $offreRepository->findBy(['online' => 1], ['created' => 'DESC']),
            'categories' => $categories
        ]);
    }
}
