<?php

namespace App\Controller;

use App\Entity\SearchService;
use App\Entity\SearchUser;
use App\Entity\User;
use App\Form\VendeurType;
use App\Repository\AvisRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VendeurController extends AbstractController
{
    #[Route('/vendeurs', name: 'vendeur')]
    public function index(): Response
    {
        return $this->render('vendeur/index.html.twig', [
            'controller_name' => 'VendeurController',
        ]);
    }

    #[Route('/compte/vendeurs/{nameUrl}', name: 'vendeur_profil')]
    public function profil(User $user, MicroserviceRepository $microserviceRepository, PaginatorInterface $paginator, Request $request, AvisRepository $avisRepository): Response
    {
        $microservices = $paginator->paginate(
            $microserviceRepository->findBy([
                'vendeur' => $user,
                'online' => true
            ], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('vendeur/profil.html.twig', [
            'user' => $user,
            'microservices' => $microservices,
            'avises' => $avisRepository->findBy(['vendeur' => $user]),
        ]);
    }

    #[Route('/vendeurs/list', name: 'vendeur_list', methods: ['GET', 'POST'])]
    public function vendeurList(
        UserRepository $userRepository,
        Request $request,
    ): Response
    {
        $searchUser = new SearchUser();
        $searchUser->page = $request->get('page', 1);
        $searchUser->setIsVerified(true);
        $form = $this->createForm(VendeurType::class, $searchUser);
        $form->handleRequest($request);


        if ($request->get('ajax') && $request->get('name')) {
            $searchUser->setName($request->get('name'));
        }
        
        $vendeurs = $userRepository->findSearch($searchUser);

        if ($request->get('ajax')) {

            return new JsonResponse([
                'content' => $this->renderView('vendeur/composants/_listing.html.twig', ['vendeurs' => $vendeurs]),
                'form' => $this->renderView('vendeur/composants/_search_form.html.twig', ['vendeurs' => $vendeurs, 'form' => $form->createView()]),
                'pagination' => $this->renderView('vendeur/composants/_pagination.html.twig', ['vendeurs' => $vendeurs]),
            ]);
        }

        return $this->renderForm('vendeur/list.html.twig', [
            'vendeurs' => $vendeurs,
            'form' => $form,
        ]);
    }
}
