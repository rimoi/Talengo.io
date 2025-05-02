<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\AvisReponse;
use App\Entity\Commande;
use App\Entity\CommandeMessage;
use App\Entity\Message;
use App\Entity\Portefeuille;
use App\Entity\Rapport;
use App\Entity\Remboursement;
use App\Form\AvisReponseType;
use App\Form\AvisType;
use App\Form\CommandeMessageType;
use App\Form\RapportType;
use App\Repository\AvisRepository;
use App\Repository\CommandeMessageRepository;
use App\Repository\CommandeRepository;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\PrixRepository;
use App\Repository\RapportRepository;
use App\Service\MailerService;
use App\Service\PaymentService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\TextUI\Command;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commandes')]
class CommandeController extends AbstractController
{
    private $privateKey;

    private $paypalkey;

    public function __construct()
    {
        /**
         * Vérification de l'environnement
         */
        if ($_ENV['APP_ENV'] === 'dev') {

            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_TEST'];

            $this->paypalkey = $_ENV['PAYPAL_SECRET_KEY_TEST'];
        } else {

            $this->privateKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];

            $this->paypalkey = $_ENV['PAYPAL_SECRET_KEY_LIVE'];
        }
    }

    #[Route('/suivis', name: 'commandes_chats')]
    public function chat(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();

        $commandes = $commandeRepository->findWhereUserIsClientOrVendeur($user);

        return $this->render('commande/chat.html.twig', [
            'usercommandes' => $commandes,
            'commande' => null,
        ]);
    }

    #[Route('/details/{id}', name: 'commandes_pack_show')]
    public function show(CommandeRepository $commandeRepository, $id): Response
    {
        $commande = $commandeRepository->findOneBy([
            'id' => $id,
            'client' => $this->getUser()
        ]);

        if (!$commande) {
            return $this->redirectToRoute('commandes_chats');
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/suivis/commande_id={id}', name: 'commande_details')]
    public function commande(
        CommandeRepository $commandeRepository,
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        CommandeMessageRepository $commandeMessageRepository,
        MailerService $mailer,
        RapportRepository $rapportRepository,
        AvisRepository $avisRepository
    ): Response {

        /** @var User $user */
        $user = $this->getUser();

        $commande = $commandeRepository->find($id);
        $tauxHoraire = $commande->getTauxHoraire() ? $commande->getTauxHoraire() : null;
        $somme = $commande->getMontant();
        $montantTotal = $somme;

        $redirect = $this->redirectToRoute('accueil', [], Response::HTTP_SEE_OTHER);

        if (!$commande) {
            return $redirect;
        }

        // Les participants de la conversation
        $particlipants = [$commande->getClient(), $commande->getVendeur()];

        if (!in_array($user, $particlipants)) {
            return $redirect;
        }

        if ($commande->getDestinataire()->getId() == $user->getId() && $commande->getLu() == 0) {
            $commande->setLu(true);
            $entityManager->flush();
        }

        // Recupération des commandes de cet utilisateur
        $usercommandes = $commandeRepository->findWhereUserIsClientOrVendeur($user);

        // Recupération des méssages liés à cette commande
        $messages = $commandeMessageRepository->findBy([
            'commande' => $commande
        ]);

        $avis = new Avis();
        $avisForm = $this->createForm(AvisType::class, $avis);
        $avisForm->handleRequest($request);

        if ($avisForm->isSubmitted() && $avisForm->isValid()) {

            $avis->setVendeur($commande->getMicroservice()->getVendeur());
            $avis->setMicroservice($commande->getMicroservice());
            $avis->setClient($user);
            $entityManager->persist($avis);
            $entityManager->flush();

            $commande->setRapportValidate(true);
            $commande->setAvis($avis);
            $entityManager->flush();

            /** Envoie du mail au client */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getClient()->getEmail(),
                'Votre avis sur la commande du service : ' . $commande->getMicroservice()->getName(),
                'mails/client/_avis.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Avis du client sur la commande du service : ' . $commande->getMicroservice()->getName(),
                'mails/_avis.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            $this->addFlash('success', "Votre avis à bien été soumis");

            return $this->redirectToRoute('commande_details', [
                'id' => $commande->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        $avisReponse = new AvisReponse();
        $avisReponseForm = $this->createForm(AvisReponseType::class, $avisReponse);
        $avisReponseForm->handleRequest($request);

        if ($avisReponseForm->isSubmitted() && $avisReponseForm->isValid()) {

            $avisReponse->setVendeur($commande->getVendeur());
            $avisReponse->setAvis($commande->getAvis());
            $entityManager->persist($avisReponse);
            $entityManager->flush();
        }

        // Test de message
        $message = new CommandeMessage();
        $form = $this->createForm(CommandeMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $destinataire = null;

            if ($user->getId() == $commande->getVendeur()->getId()) {
                $destinataire = $commande->getClient();
            } else {
                $destinataire = $commande->getVendeur();
            }

            $commande->setDestinataire($destinataire);
            $commande->setLu(false);

            $message->setCommande($commande);
            $message->setUser($this->getUser());
            $message->setLu(false);
            $entityManager->persist($message);
            $entityManager->flush();


            return $this->redirectToRoute('commande_details', [
                'id' => $commande->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        /** Rapport de fin de prestation */
        $rapport = new Rapport();
        $rapportForm = $this->createForm(RapportType::class, $rapport);
        $rapportForm->handleRequest($request);

        if ($rapportForm->isSubmitted() && $rapportForm->isValid()) {

            $portefeuille = $commande->getVendeur()->getPortefeuille();

            //dd($conversation);

            $somme = $commande->getMontant() + $portefeuille->getSoldeDisponible();

            $difference = null;

            if ($commande->getMontant() >= $portefeuille->getSoldeEncours()) {

                $difference = $commande->getMontant() - $portefeuille->getSoldeEncours();
            } else {

                $difference = $portefeuille->getSoldeEncours() - $commande->getMontant();
            }

            $portefeuille->setSoldeDisponible($somme);
            $portefeuille->setSoldeEncours($difference);
            $entityManager->flush();

            $rapport->setCommande($commande);
            $entityManager->persist($rapport);
            $entityManager->flush();

            $commande->setRapport($rapport);
            $commande->setRapportValidate(true);
            $commande->setRapportValidateAt(new \DateTimeImmutable());
            $entityManager->flush();

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Commande livrée',
                'mails/_rapport_livrer.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            $this->addFlash('success', 'Rapport envoyé avec succès');
            return $this->redirectToRoute('commande_details', [
                'id' => $commande->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/suivis.html.twig', [
            'rapport' => $rapportRepository->findOneBy(['commande' => $commande]),
            'commande' => $commande,
            'avisForm' => $avisForm->createView(),
            'rapportForm' => $rapportForm->createView(),
            'avisReponseForm' => $avisReponseForm->createView(),
            'usercommandes' => $usercommandes,
            'userserviceAvis' => $avisRepository->findOneBy([
                'client' => $user,
                'vendeur' => $commande->getMicroservice()->getVendeur(),
                'microservice' => $commande->getMicroservice()
            ]),
            'messages' => $messages,
            'form' => $form->createView(),
            'montant' => $montantTotal,
            'tauxHoraire' => $tauxHoraire,
        ]);
    }

    #[Route('/success/commande_id={id}', name: 'commande_success')]
    public function success(Commande $commande): Response
    {
        if ($commande->getClient() != $this->getUser()) {
            return $this->redirectToRoute('accueil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/success.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/commander/{slug}/{offre}', name: 'commander_microservice', methods: ['GET', 'POST'])]
    public function checkout(Request $request, EntityManagerInterface $entityManager, CommandeRepository $commandeRepository, MicroserviceRepository $microserviceRepository, PrixRepository $prixRepository, $slug, $offre, PaymentService $paymentService): Response
    {
        $microservice = $microserviceRepository->findOneBy(['slug' => $slug]);

        $montant = null;

        if ($offre == 'Mastering') {
            $montant = $microservice->getPrixMastering();
        } elseif ($offre == 'Mixage') {
            $montant = $microservice->getPrixMixage();
        } elseif ($offre == 'Beatmaking') {
            $montant = $microservice->getPrixBeatmaking();
        } elseif ($offre == 'Composition') {
            $montant = $microservice->getPrixComposition();
        }

        $directory = $this->redirectToRoute('microservices');

        if (!$microservice) {
            return $directory;
        }

        $portefeuille = $microservice->getVendeur()->getPortefeuille();

        if (!$portefeuille) {
            $portefeuille = new Portefeuille();
            $portefeuille->setSoldeEncours(0);
            $portefeuille->setSoldeDisponible(0);
            $portefeuille->setVendeur($microservice->getVendeur());
            $entityManager->persist($portefeuille);
            $entityManager->flush();
        }

        // Calcul des taxes
        $taux = (0.015 * $montant) + 0.25;
        $frais = 0.30;
        $somme = $montant + $taux + $frais;

        $order = [
            'purchase_units' => [[
                'description'    => 'Talengo.io achats de prestation',
                'items'   =>  [
                    'name'  =>  $microservice->getName(),
                    'quatity'   =>  1,
                    'unit_amount'   =>  [
                        'value'     =>  $somme,
                        'currency_code' =>  'EUR',
                    ],
                ],

                'amount'  =>  [
                    'currency_code' =>  'EUR',
                    'value'         =>  $somme,
                ]
            ]]
        ];

        // Paypal infos
        $userTest = 'sb-rw3oo17429039@personal.example.com';
        $sandBoxId = $this->paypalkey;

        // Stripe payment
        if ($somme > 0) {

            // Instanciation Stripe
            \Stripe\Stripe::setApiKey($this->privateKey);

            $intent = \Stripe\PaymentIntent::create([
                'amount'    =>  number_format((float)$somme, 2, '.', '') * 100,
                'currency'  =>  'eur',
                'payment_method_types'  =>  ['card']
            ]);
            // Traitement du formulaire Stripe
            //dd($intent);

            if ($request->getMethod() === "POST") {

                if ($intent['status'] === "requires_payment_method") {
                    // TODO

                }
            }
        } else {
            //dd('aucun prix');
        }

        return $this->render('commande/checkout.html.twig', [
            'intentSecret'    =>  $intent['client_secret'],
            'intent'    => $intent,
            'intentId'    => $intent['id'],
            'frais'    => number_format((float)$frais, 2, '.', ''),
            'taux'    => number_format((float)$taux, 2, '.', ''),
            'total'    => number_format((float)$somme, 2, '.', ''),
            'microservice' => $microservice,
            'type_offre' => $offre,
            'montant' => $montant,
            'clientId' => $sandBoxId,
        ]);
    }

    #[Route('/save-commande/{slug}/{offre}/{payment_intent}', name: 'save_commande')]
    public function save(MicroserviceRepository $microserviceRepository, EntityManagerInterface $entityManager, $slug, $offre, PrixRepository $prixRepository, $payment_intent, MailerService $mailer): Response
    {
        $microservice = $microserviceRepository->findOneBy(['slug' => $slug]);

        $montant = null;

        if ($offre == 'Mastering') {
            $montant = $microservice->getPrixMastering();
        } elseif ($offre == 'Mixage') {
            $montant = $microservice->getPrixMixage();
        } elseif ($offre == 'Beatmaking') {
            $montant = $microservice->getPrixBeatmaking();
        } elseif ($offre == 'Composition') {
            $montant = $microservice->getPrixComposition();
        }else {
            # code...
        }
        
        // Calcul des taxes
        $taux = (0.015 * $montant) + 0.25;
        $frais = 0.30;
        $somme = $montant + $taux + $frais;

        $user = $this->getUser();
        $commande = new Commande();
        $commande->setMicroservice($microservice);
        $commande->setReservationDate(new \DateTime());
        $commande->setPaymentIntent($payment_intent);
        $commande->setClient($user);
        $commande->setVendeur($microservice->getVendeur());
        $commande->setDestinataire($microservice->getVendeur());
        $commande->setConfirmationClient(false);
        $commande->setLu(false);
        $commande->setStatut('En attente');

        $commande->setMontant($somme);
        $commande->setOffre($offre);
        $commande->setValidate(false);
        $commande->setDeliver(false);
        $commande->setCancel(false);

        $entityManager->persist($commande);
        $entityManager->flush();

        /** Envoie du mail au client */
        $mailer->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getClient()->getEmail(),
            'Nouvelle commande',
            'mails/_client.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande
        );

        /** Envoie du mail au vendeur */
        $mailer->sendCommandMail(
            'talengo.contact@gmail.com',
            $commande->getVendeur()->getEmail(),
            'Nouvelle commande',
            'mails/_vendeur.html.twig',
            $commande->getClient(),
            $commande->getVendeur(),
            $commande
        );

        return $this->redirectToRoute('commande_success', [
            'id' => $commande->getId()
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vendeur/commande/validate/{id}', name: 'vendeur_valider_commande', methods: ['POST'])]
    public function vendeurValiderCommande(
        Request $request,
        CommandeRepository $commandeRepository,
        $id,
        MailerService $mailer,
        EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);

        if ($this->isCsrfTokenValid('validate' . $commande->getId(), $request->request->get('_token'))) {

            $portefeuille = $commande->getVendeur()->getPortefeuille();

            $somme = $commande->getMontant() + $portefeuille->getSoldeEncours();
            $portefeuille->setSoldeEncours($somme);

            $commande->setValidate(true);
            $commande->setDeliver(false);
            $commande->setStatut('Valider');
            $commande->setCancel(false);
            $commande->setValidateAt(new \DateTimeImmutable());
            $entityManager->flush();

            /** Envoie du mail au client */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getClient()->getEmail(),
                'Commande validée',
                'mails/client/_commande_valider.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Commande validée',
                'mails/_commande_valider.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );
        }

        $this->addFlash('success', 'Commande validée!');

        return $this->redirectToRoute('commande_details', [
            'id' => $commande->getId()
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vendeur/commande/livrer/{id}', name: 'vendeur_livrer_commande', methods: ['POST'])]
    public function vendeurLivrerCommande(
        Request $request,
        CommandeRepository $commandeRepository,
        $id,
        MailerService $mailer,
        EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);

        if ($this->isCsrfTokenValid('livrer' . $commande->getId(), $request->request->get('_token'))) {

            $commande->setDeliver(true);
            $commande->setDeliverAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Commande livrée!');

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Commande livrée',
                'mails/_commande_livrer.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );
        }

        return $this->redirectToRoute('commande_details', [
            'id' => $commande->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vendeur/commande/annuler/{id}', name: 'vendeur_annuler_commande', methods: ['POST'])]
    public function vendeurAnnulerCommande(
        Request $request,
        CommandeRepository $commandeRepository,
        $id,
        MailerService $mailer,
        EntityManagerInterface $entityManager
    ): Response {
        $commande = $commandeRepository->find($id);

        if ($this->isCsrfTokenValid('annuler' . $commande->getId(), $request->request->get('_token'))) {

            /** Annulation de la commande entraine un remboursement Stripe */
            \Stripe\Stripe::setApiKey($this->privateKey);

            try {
                $refound = \Stripe\Refund::create([
                    "payment_intent" => $commande->getPaymentIntent(),
                    "reason" => "requested_by_customer",
                    //"receipt_number" => "4242 4242 4242 4242",
                    //"source_transfer_reversal" => null,
                    //"status" => "succeeded",
                ]);
            } catch (\Throwable $th) {
                dd($th->getMessage());
            }

            $remboursement = new Remboursement();
            $remboursement->setUser($commande->getClient());
            $remboursement->setVendeur($commande->getVendeur());
            $remboursement->setCommande($commande);
            $remboursement->setMontant($commande->getMontant());
            $remboursement->setMotif("Commande annulée par le prestataire");
            $remboursement->setStatut("Annuler");
            $entityManager->persist($remboursement);
            $entityManager->flush();

            $portefeuille = $commande->getVendeur()->getPortefeuille();

            if ($commande->getMontant() >= $portefeuille->getSoldeEncours()) {

                $difference = $commande->getMontant() - $portefeuille->getSoldeEncours();
            } else {

                $difference = $portefeuille->getSoldeEncours() - $commande->getMontant();
            }

            $portefeuille->setSoldeEncours($difference);

            $commande->setCancel(true);
            $commande->setStatut('Annuler');
            $commande->setDeliver(false);
            $commande->setValidate(false);
            $commande->setCancelAt(new \DateTimeImmutable());
            $entityManager->flush();

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Commande annulée',
                'mails/_commande_annuler.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );
        }

        $this->addFlash('success', 'Commande annulée avec succès!');

        return $this->redirectToRoute('commande_details', [
            'id' => $commande->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reservation/{slug}/{commande}', name: 'commander_microservice_reservation', methods: ['GET', 'POST'])]
    public function reservation(Request $request, EntityManagerInterface $entityManager, CommandeRepository $commandeRepository, MicroserviceRepository $microserviceRepository, $slug, PaymentService $paymentService, Commande $commande): Response
    {
        $microservice = $microserviceRepository->findOneBy(['slug' => $slug]);
        $montantTotal = $commande->getMontant();
        $tauxHoraire = false;

        if ($commande->getTauxHoraire()) {

            $tauxHoraire = $commande->getTauxHoraire();

            /** Si taux erreur == 0 taux erreur == 1 */
            if ($tauxHoraire == 0)
                $tauxHoraire = 1;

            $montantTotal = $commande->getMontant();
        }

        $directory = $this->redirectToRoute('microservices');

        if (!$microservice) {
            return $directory;
        }

        $options = $microservice->getServiceOptions();

        $portefeuille = $microservice->getVendeur()->getPortefeuille();

        if (!$portefeuille) {
            $portefeuille = new Portefeuille();
            $portefeuille->setSoldeEncours(0);
            $portefeuille->setSoldeDisponible(0);
            $portefeuille->setVendeur($microservice->getVendeur());
            $entityManager->persist($portefeuille);
            $entityManager->flush();
        }

        // Calcul des taxes
        $taux = (0.015 * $montantTotal) + 0.25;
        $frais = 0.30;
        $somme = $montantTotal + $taux + $frais;

        $order = [
            'purchase_units' => [[
                'description'    => 'Talengo.io achats de prestation',
                'items'   =>  [
                    'name'  =>  $microservice->getName(),
                    'quatity'   =>  1,
                    'unit_amount'   =>  [
                        'value'     =>  $somme,
                        'currency_code' =>  'EUR',
                    ],
                ],

                'amount'  =>  [
                    'currency_code' =>  'EUR',
                    'value'         =>  $somme,
                ]
            ]]
        ];

        // Paypal infos
        $userTest = 'sb-rw3oo17429039@personal.example.com';
        $sandBoxId = $this->paypalkey;

        // Instanciation Stripe
        \Stripe\Stripe::setApiKey($this->privateKey);

        $intent = \Stripe\PaymentIntent::create([
            'amount'    =>  number_format((float)$somme, 2, '.', '') * 100,
            'currency'  =>  'eur',
            'payment_method_types'  =>  ['card']
        ]);
        // Traitement du formulaire Stripe
        //dd($intent['id']);

        if ($request->getMethod() === "POST") {

            if ($intent['status'] === "requires_payment_method") {
                // TODO

            }
        }

        return $this->render('commande/reservation.html.twig', [
            'intentSecret'    =>  $intent['client_secret'],
            'intent'    => $intent,
            'intentId'    => $intent['id'],
            'commande'    => $commande,
            'frais'    => number_format((float)$frais, 2, '.', ''),
            'taux'    => number_format((float)$taux, 2, '.', ''),
            'total'    => number_format((float)$somme, 2, '.', ''),
            'microservice' => $microservice,
            'type_offre' => 'reservation',
            'montant' => $montantTotal,
            'tauxHoraire' => $tauxHoraire,
            'clientId' => $sandBoxId,
        ]);
    }

    #[Route('/save-reservation/{slug}/{payment_intent}/{commande}', name: 'save_reservation')]
    public function savereservation(MicroserviceRepository $microserviceRepository, EntityManagerInterface $entityManager, $slug, PrixRepository $prixRepository, $payment_intent, Commande $commande, MailerService $mailer): Response
    {
        $microservice = $microserviceRepository->findOneBy(['slug' => $slug]);
        $tauxHoraire = 1;
        
        // Calcul des taxes
        $taux = (0.015 * $commande->getMontant()) + 0.25;
        $frais = 0.30;
        $somme = $commande->getMontant() + $taux + $frais;

        if ($commande->getTauxHoraire()) {
            $tauxHoraire = $commande->getTauxHoraire();
        }

        if ($commande->isPayed() == false or $commande->isPayed() == null) {

            $commande->setPaymentIntent($payment_intent);
            $commande->setMontant($somme);
            $commande->setClient($this->getUser());
            $commande->setStatut('Valider');
            $commande->setPayed(true);
            $entityManager->flush();

            /** Envoie du mail au client */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getClient()->getEmail(),
                'Nouvelle commande',
                'mails/_client.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Nouvelle commande',
                'mails/_vendeur.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );
        }

        return $this->redirectToRoute('commande_success', [
            'id' => $commande->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
