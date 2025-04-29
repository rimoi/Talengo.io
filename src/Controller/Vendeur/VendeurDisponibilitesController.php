<?php

namespace App\Controller\Vendeur;

use App\Entity\Disponibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendeur')]
class VendeurDisponibilitesController extends AbstractController
{
    #[Route('/service-disponibilite-delete/{id}', name: 'vendeur_services_disponibilite_delete', methods: ['POST'])]
    public function delete(Request $request, Disponibilite $disponibilite, 
    EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$disponibilite->getId(), $request->request->get('_token'))) {
            $service = $disponibilite->getService();
            $entityManager->remove($disponibilite);
            $entityManager->flush();

            $this->addFlash('success', "La disponibilité pour ce service a bien été supprimer");
        }

        return $this->redirectToRoute('vendeur_microservices_disponibilite', [
            'id' => $service->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
