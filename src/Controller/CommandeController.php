<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\AvisReponse;
use App\Entity\Commande;
use App\Entity\CommandeMessage;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Portefeuille;
use App\Entity\Rapport;
use App\Entity\Remboursement;
use App\Entity\Retouche;
use App\Entity\User;
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
use Omnipay\Omnipay;
use PHPUnit\TextUI\Command;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/commandes')]
class CommandeController extends AbstractController
{
    private $privateKey;

    private $paypalkey;

    public function __construct()
    {
        $this->privateKey = $_ENV['STRIPE_SECRET_KEY'];

        $this->paypalkey = $_ENV['PAYPAL_SECRET'];
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
        CommandeRepository        $commandeRepository,
                                  $id,
        Request                   $request,
        EntityManagerInterface    $entityManager,
        CommandeMessageRepository $commandeMessageRepository,
        MailerService             $mailer,
        RapportRepository         $rapportRepository,
        AvisRepository            $avisRepository
    ): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        /** @var Commande $commande */
        $commande = $commandeRepository->find($id);
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

        $conversation = $entityManager->getRepository(Conversation::class)->findOneBy([
            'user1' => $commande->getClient()->getId(),
            'user2' => $commande->getVendeur()->getId(),
            'microservice' => $commande->getMicroservice()->getId()
        ]);


        $oldMessages = [];
        if ($conversation) {
            $oldMessages = $entityManager->getRepository(Message::class)->findBy(['conversation' => $conversation]);
        }

        $messages = $commandeMessageRepository->findBy([
            'commande' => $commande
        ]);

        if (count($oldMessages)) {
            $messageSeparated = new Message();

            $date = $commande->getReservationDate();

            $formatter = new \IntlDateFormatter(
                'fr_FR',
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::SHORT,
                'Europe/Paris', // Fuseau horaire
                \IntlDateFormatter::GREGORIAN,
                "d MMM y à HH:mm"
            );

            $messageSeparated->setContenu(
                sprintf('Commande passée le %s', $formatter->format($date))
            );

            $messages = array_merge($oldMessages, [$messageSeparated], $messages);
        }

        $avis = new Avis();
        $avisForm = $this->createForm(AvisType::class, $avis);
        $avisForm->handleRequest($request);

        if ($avisForm->isSubmitted() && $avisForm->isValid()) {

            if ($avis->getType() == 'Positif') {
                $commande->getMicroservice()->getVendeur()->incrementAvis();
                $commande->getMicroservice()->incrementAvis();
            }

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


            $somme = $commande->getMontant() + $portefeuille->getSoldeDisponible();

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
                'Votre argent arrive !',
                'mails/_rapport_livrer.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            $this->addFlash('success', 'Le rapport a été envoyé avec succès.');
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
        ]);
    }

    #[Route('/success/str/commande_id={id}', name: 'commande_str_success')]
    public function successStripe(Commande $commande, MailerService $mailer): Response
    {
        if ($commande->getClient() != $this->getUser()) {
            return $this->redirectToRoute('accueil', [], Response::HTTP_SEE_OTHER);
        }

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

        return $this->render('commande/success.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/success/pyp/commande_id={id}', name: 'commande_pyp_success')]
    public function successPaypal(Request $request, Commande $commande, EntityManagerInterface $entityManager, MailerService $mailer): Response
    {
        if ($commande->getClient() != $this->getUser()) {
            return $this->redirectToRoute('accueil', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->get('paymentId') && $request->get('PayerID')) {
            $gateway = Omnipay::create('PayPal_Rest');
            $gateway->setClientId($_ENV['PAYPAL_CLIENT_ID']);
            $gateway->setSecret($_ENV['PAYPAL_SECRET']);

            if ($_ENV['APP_ENV'] === 'dev') {
                $gateway->setTestMode(true);
            }

            $operation = $gateway->completePurchase([
                'payer_id' => $request->get('PayerID'),
                'transactionReference' => $request->get('paymentId'),
            ]);

            $response = $operation->send();

            if ($response->isSuccessful()) {
                $payment = $response->getData();

                $saleId = null;
                if (isset($payment['transactions'][0]['related_resources'][0]['sale']['id'])) {
                    $saleId = $payment['transactions'][0]['related_resources'][0]['sale']['id'];
                }

                $commande->setReferencePaypalId($saleId);

                $commande->setPayerPaypalId($payment['payer']['payer_info']['payer_id'] ?? null);
                $commande->setPayerEmailPaypal($payment['payer']['payer_info']['email'] ?? null);

                $commande->setIsPayWithStripe(false);

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

            return $this->render('commande/success.html.twig', [
                'commande' => $commande
            ]);
        }


        return $this->render('commande/error.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/error/commande_id={id}', name: 'commande_error')]
    public function error(Commande $commande): Response
    {
        if ($commande->getClient() != $this->getUser()) {
            return $this->redirectToRoute('accueil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/error.html.twig', [
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
                'description' => 'Talengo.io achats de prestation',
                'items' => [
                    'name' => $microservice->getName(),
                    'quatity' => 1,
                    'unit_amount' => [
                        'value' => $somme,
                        'currency_code' => 'EUR',
                    ],
                ],

                'amount' => [
                    'currency_code' => 'EUR',
                    'value' => $somme,
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
                'amount' => number_format((float)$somme, 2, '.', '') * 100,
                'currency' => 'eur',
                'payment_method_types' => ['card']
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
            'intentSecret' => $intent['client_secret'],
            'intent' => $intent,
            'intentId' => $intent['id'],
            'frais' => number_format((float)$frais, 2, '.', ''),
            'taux' => number_format((float)$taux, 2, '.', ''),
            'total' => number_format((float)$somme, 2, '.', ''),
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
        } else {
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

        return $this->redirectToRoute('commande_str_success', [
            'id' => $commande->getId()
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vendeur/commande/validate/{id}', name: 'vendeur_valider_commande', methods: ['POST'])]
    public function vendeurValiderCommande(
        Request                $request,
        CommandeRepository     $commandeRepository,
                               $id,
        MailerService          $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {
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
                'Votre commande a été validée',
                'mails/client/_commande_valider.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Confirmation – Vous avez accepté une commande',
                'mails/_commande_valider.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );
        }

        $this->addFlash('success', 'Commande validée !');

        return $this->redirectToRoute('commande_details', [
            'id' => $commande->getId()
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vendeur/commande/livrer/{id}', name: 'vendeur_livrer_commande', methods: ['POST'])]
    public function vendeurLivrerCommande(
        Request                $request,
        CommandeRepository     $commandeRepository,
                               $id,
        MailerService          $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {
        $commande = $commandeRepository->find($id);

        if ($this->isCsrfTokenValid('livrer' . $commande->getId(), $request->request->get('_token'))) {

            $commande->setDeliver(true);
            $commande->setDeliverAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Commande livrée 🚚 !');

            /** Envoie du mail au client */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getClient()->getEmail(),
                'Commande livrée 🚚',
                'mails/_commande_livrer_client.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Commande livrée 🚚',
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

    #[Route('/vendeur/commande/retouche/{id}', name: 'vendeur_retouche_commande', methods: ['POST'])]
    public function vendeurRetoucheCommande(
        Request                $request,
        CommandeRepository     $commandeRepository,
                               $id,
        MailerService          $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {
        $commande = $commandeRepository->find($id);


        if ($this->isCsrfTokenValid('retouche' . $commande->getId(), $request->request->get('_token'))) {

            $retouche = new Retouche();
            $retouche->setCommande($commande);
            $commande->addRetouche($retouche);

            $entityManager->persist($retouche);
            $entityManager->flush();

            $this->addFlash('success', "Votre demande de retouche a bien été envoyée !");

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Nouvelle demande de retouche sur la commande',
                'mails/_commande_retouche.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );
        }

        return $this->redirectToRoute('commande_details', [
            'id' => $commande->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vendeur/commande/retouche/{id}/valider', name: 'vendeur_retouche_valider_commande', methods: ['POST'])]
    public function vendeurRetoucheValiderCommande(
        Request                $request,
        CommandeRepository     $commandeRepository,
                               $id,
        MailerService          $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {
        $commande = $commandeRepository->find($id);


        if ($this->isCsrfTokenValid('retouche' . $commande->getId(), $request->request->get('_token'))) {

            $retouche = $commande->getRetouches()->last();

            if (!$retouche) {
                throw $this->createNotFoundException('Retouche introuvable !');
            }

            $retouche->setFinished(true);
            $retouche->setFinRetoucheDate(new \DateTime());

            $entityManager->flush();

            $this->addFlash('success', "Votre correction a bien été transmise au client !");

            /** Envoie du mail au client */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getClient()->getEmail(),
                'Retouche effectuée ✅',
                'mails/_commande_retouche_effectuer.html.twig',
                $commande->getClient(),
                $commande->getVendeur(),
                $commande
            );
        }

        return $this->redirectToRoute('commande_details', [
            'id' => $commande->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vendeur/commande/cloturer/{id}', name: 'vendeur_cloturer_commande', methods: ['POST'])]
    public function vendeurCloturerCommande(
        Request                $request,
        CommandeRepository     $commandeRepository,
                               $id,
        MailerService          $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {
        $commande = $commandeRepository->find($id);

        if ($this->isCsrfTokenValid('cloturer' . $commande->getId(), $request->request->get('_token'))) {

            $commande->setCloturer(true);
            $commande->setCloturerDate(new \DateTime());

            $entityManager->flush();

            $this->addFlash('success', 'Commande clôturée ✅ !');

            /** Envoie du mail au vendeur */
            $mailer->sendCommandMail(
                'talengo.contact@gmail.com',
                $commande->getVendeur()->getEmail(),
                'Commande clôturée ✅',
                'mails/_commande_terminer.html.twig',
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
        Request                $request,
        CommandeRepository     $commandeRepository,
                               $id,
        MailerService          $mailer,
        EntityManagerInterface $entityManager
    ): Response
    {
        $commande = $commandeRepository->find($id);

        if ($this->isCsrfTokenValid('annuler' . $commande->getId(), $request->request->get('_token'))) {

            if ($commande->isIsPayWithStripe()) {
                $gateway = Omnipay::create('Stripe');
                $gateway->setApiKey($this->privateKey);

                $response = $gateway->refund([
                    'transactionReference' => $commande->getReferenceStripeId(),
                    'amount' => $commande->getMontant(), // Optionnel, pour remboursement partiel
                ])->send();

                if ($response->isSuccessful()) {
                    // Remboursement OK
                    $refundId = $response->getTransactionReference();
                    $commande->setReferenceStripeRefundId($refundId);

                    $commande->setStatut('Annulé');

                    $commande->setIsPayWithStripe(null);
                } else {

                    $commande->setStripeErrorRefund($response->getMessage());

                    $entityManager->flush();

                    $this->addFlash('danger', "La commande n'a pas pu être annulée. Contactez l'administrateur du site.");

                    return $this->redirectToRoute('commande_details', [
                        'id' => $commande->getId(),
                    ], Response::HTTP_SEE_OTHER);
                }
            } elseif ($commande->isIsPayWithStripe() === false) {
                $gateway = Omnipay::create('PayPal_Rest');
                $gateway->setClientId($_ENV['PAYPAL_CLIENT_ID']);
                $gateway->setSecret($_ENV['PAYPAL_SECRET']);

                if ($_ENV['APP_ENV'] === 'dev') {
                    $gateway->setTestMode(true);
                }

                $response = $gateway->refund([
                    'transactionReference' => $commande->getReferencePaypalId(),
                    'amount' => number_format($commande->getMontant(), 2, '.', ''),
                    'currency' => 'EUR',
                ])->send();

                if ($response->isSuccessful()) {
                    $refundData = $response->getData();

                    $commande->setReferencePaypalRefundId($refundData['id']);

                    $commande->setStatut('Annulé');

                    $commande->setIsPayWithStripe(null);
                } else {

                    $commande->setPaypalErrorRefund($response->getMessage());

                    $entityManager->flush();

                    $this->addFlash('danger', "La commande n'a pas pu être annulée. Contactez l'administrateur du site.");

                    return $this->redirectToRoute('commande_details', [
                        'id' => $commande->getId(),
                    ], Response::HTTP_SEE_OTHER);
                }

            } else {
                throw $this->createNotFoundException("Impossible d'identifier la méthode de paiement utilisé !");
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
    public function reservation(
        Request                $request,
        EntityManagerInterface $entityManager,
        MicroserviceRepository $microserviceRepository,
                               $slug,
        Commande               $commande,
        PaymentService         $paymentService
    ): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($currentUser->hasRole('ROLE_VENDEUR')) {
            throw $this->createAccessDeniedException("Actuellement, les vendeurs ne peuvent pas réserver les services d'autres vendeurs. Mais rassurez-vous, nous y travaillons : cette option est prévue et arrivera très prochainement !");
        }

        $microservice = $microserviceRepository->findOneBy(['slug' => $slug]);


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

        $errorMessage = null;


        if ($request->getMethod() === "POST") {

            if ($this->isCsrfTokenValid('reserver' . $commande->getId(), $request->request->get('_token'))) {

                if ($request->get('montant')) {
                    $commande->setMontant((float)$request->get('montant'));
                    $entityManager->flush();
                }

                if ($request->get('payment') === 'card') {

                    $paymentService->hydrateInfo($request, $this->getUser());

                    if ($request->get('montant')) {
                        $commande->setMontant((float)$request->get('montant'));
                    }

                    $entityManager->flush();

                    $gateway = Omnipay::create('Stripe');
                    $gateway->setApiKey($this->privateKey);

                    $response = $gateway->purchase([
                        'amount' => $commande->getMontant(),
                        'currency' => 'EUR',
                        'token' => $request->get('stripeToken'),
                    ])->send();

                    if ($response->isSuccessful()) {

                        $commande->setReferenceStripeId($response->getTransactionReference());
                        $commande->setPayed(true);

                        $commande->setReservationDate(new \DateTime());

                        $commande->setIsPayWithStripe(true);

                        $entityManager->flush();

                        return $this->redirectToRoute('commande_str_success', [
                            'id' => $commande->getId()
                        ], Response::HTTP_SEE_OTHER);
                    } else {
                        return $this->redirectToRoute('commande_error', [
                            'id' => $commande->getId()
                        ], Response::HTTP_SEE_OTHER);
                    }
                } else {

                    if ($request->get('montant')) {
                        $commande->setMontant((float)$request->get('montant'));
                        $entityManager->flush();
                    }

                    $gateway = Omnipay::create('PayPal_Rest');
                    $gateway->setClientId($_ENV['PAYPAL_CLIENT_ID']);
                    $gateway->setSecret($_ENV['PAYPAL_SECRET']);

                    if ($_ENV['APP_ENV'] === 'dev') {
                        $gateway->setTestMode(true);
                    }

                    $response = $gateway->purchase([
                        'amount' => $commande->getMontant(),
                        'currency' => $_ENV['PAYPAL_CURRENCY'],
                        'returnUrl' => $this->generateUrl('commande_pyp_success', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                        'cancelUrl' => $this->generateUrl('commande_error', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ])->send();

                    if ($response->isRedirect()) {
                        $commande->setPayed(true);

                        $commande->setReservationDate(new \DateTime());

                        $entityManager->flush();

                        return $response->redirect();
                    } elseif ($response->isSuccessful()) {

                        $commande->setPayed(true);

                        $commande->setReservationDate(new \DateTime());

                        $entityManager->flush();

                        return $this->redirectToRoute('commande_pyp_success', [
                            'id' => $commande->getId()
                        ], Response::HTTP_SEE_OTHER);
                    } else {
                        return $this->redirectToRoute('commande_error', [
                            'id' => $commande->getId()
                        ], Response::HTTP_SEE_OTHER);
                    }
                }
            }
        }

        return $this->render('commande/reservation2.html.twig', [
            'commande' => $commande,
            'microservice' => $microservice,
            'errorMessage' => $errorMessage,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            'paypal_client_secret' => $_ENV['PAYPAL_CLIENT_ID'],
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

        return $this->redirectToRoute('commande_str_success', [
            'id' => $commande->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
