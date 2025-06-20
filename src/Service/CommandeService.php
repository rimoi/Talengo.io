<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\ServiceOption;

class CommandeService
{

    public function createCommande($slug): Commande
    {
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
            $commande->setLu(false);
            $commande->setStatut('Non payée');
            $commande->setOffre('Reservation');
            $commande->setValidate(false);
            $commande->setDeliver(false);
            $commande->setCancel(false);
            $entityManager->persist($commande);
            $entityManager->flush();
        }
    }
}