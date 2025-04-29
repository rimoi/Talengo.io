<?php

namespace App\Controller\Admin;

use App\Entity\Microservice;
use App\Entity\ServiceSignale;
use App\Form\ServiceSignaleType;
use App\Repository\ServiceSignaleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/services-signaler')]
class AdminServicesSignaleController extends AbstractController
{
    #[Route('/', name: 'app_admin_services_signale_index', methods: ['GET'])]
    public function index(ServiceSignaleRepository $serviceSignaleRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $service_signales = $paginator->paginate(
            $serviceSignaleRepository->findBy([], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/admin_services_signale/index.html.twig', [
            'service_signales' => $service_signales,
        ]);
    }

    #[Route('/new', name: 'app_admin_services_signale_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ServiceSignaleRepository $serviceSignaleRepository): Response
    {
        $serviceSignale = new ServiceSignale();
        $form = $this->createForm(ServiceSignaleType::class, $serviceSignale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serviceSignaleRepository->save($serviceSignale, true);

            return $this->redirectToRoute('app_admin_services_signale_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_services_signale/new.html.twig', [
            'service_signale' => $serviceSignale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_services_signale_show', methods: ['GET'])]
    public function show(ServiceSignale $serviceSignale, ServiceSignaleRepository $serviceSignaleRepository): Response
    {
        if ($serviceSignale->isLu() == false) {
            $serviceSignale->setLu(true);
            $serviceSignaleRepository->save($serviceSignale, true);
        }

        return $this->render('admin/admin_services_signale/show.html.twig', [
            'service_signale' => $serviceSignale,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_services_signale_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ServiceSignale $serviceSignale, ServiceSignaleRepository $serviceSignaleRepository): Response
    {
        $form = $this->createForm(ServiceSignaleType::class, $serviceSignale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serviceSignaleRepository->save($serviceSignale, true);

            return $this->redirectToRoute('app_admin_services_signale_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_services_signale/edit.html.twig', [
            'service_signale' => $serviceSignale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_services_signale_delete', methods: ['POST'])]
    public function delete(Request $request, ServiceSignale $serviceSignale, ServiceSignaleRepository $serviceSignaleRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$serviceSignale->getId(), $request->request->get('_token'))) {
            $serviceSignaleRepository->remove($serviceSignale, true);
        }

        return $this->redirectToRoute('app_admin_services_signale_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/offline/{id}', name: 'app_admin_services_signale_offline', methods: ['POST'])]
    public function offline(Request $request, Microservice $microservice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('offline' . $microservice->getId(), $request->request->get('_token'))) {
            //dd($microservice);
            $microservice->setOffline(1);
            $entityManager->flush();
            $this->addFlash('success', 'Le contenu a bien été bloqué, il ne sera plus visible sur la plateforme.');
        }

        return $this->redirectToRoute('app_admin_services_signale_index', [], Response::HTTP_SEE_OTHER);
    }
}
