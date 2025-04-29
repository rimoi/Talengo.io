<?php

namespace App\Controller;

use App\Repository\AbonnementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook')]
class WebhookController extends AbstractController
{
    private $secretKey;

    public function __construct()
    {
        /**
         * Vérification de l'environnement
         */
        if ($_ENV['APP_ENV'] === 'dev') {

            $this->secretKey = $_ENV['STRIPE_SECRET_KEY_TEST'];
        } else {

            $this->secretKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];
        }
    }

    #[Route('/stripe', name: 'app_webhook_stripe', methods: ['POST', 'GET'])]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, LoggerInterface $logger, AbonnementRepository $abonnementRepository): Response
    {
        $stripe = new \Stripe\StripeClient($this->getParameter('stripe_webhook_secret'));

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = 'whsec_d1bfb50297332365cee4cb040875b22bc1e0c190a2328410a105201d9c24f0f5';

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $abonnementId = $session->subscription;

                $stripe = new \Stripe\StripeClient($this->secretKey);
                $abonnementStripe = $stripe->subscriptions->retrieve($abonnementId, []);
                $planId = $abonnementStripe->plan->id;

                // Get User
                $customerEmail = $session->customer_details->email;
                $user = $userRepository->findOneBy(['email' => $customerEmail]);

                if (!$user) {
                    $logger->info('Webhook Stripe user not found');
                    http_response_code(404);
                    exit();
                }

                // Disable old subscription
                $activeAbonnement = $abonnementRepository->findOneBy(['user' => $user]);
                if ($activeAbonnement) {
                    \Stripe\Subscription::update(
                        $activeAbonnement->getStripeId(),
                        [
                            'cancel_at_period_end' => false
                        ]
                    );

                    $activeAbonnement->setIsActive(false);
                    $activeAbonnement->setIsCanceled(true);
                    $entityManager->flush();

                }


            case 'invoice.paid':
                $invoice = $event->data->object;
                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);

        $response = new Response('success');
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
