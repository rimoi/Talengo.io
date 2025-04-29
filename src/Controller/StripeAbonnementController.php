<?php

namespace App\Controller;

use App\Repository\AbonnementRepository;
use App\Entity\Abonnement;
use App\Entity\Portefeuille;
use App\Repository\StripeRepository;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Error;

#[Route('/abonnements')]
class StripeAbonnementController extends AbstractController
{
   private $privateKey;

   private UrlHelper $urlHelper;

   public function __construct(UrlHelper $urlHelper)
   {
      // Vérification de l'environnement

      if ($_ENV['APP_ENV'] === 'dev') {

         $this->privateKey = $_ENV['STRIPE_SECRET_KEY_TEST'];
      } else {

         $this->privateKey = $_ENV['STRIPE_SECRET_KEY_LIVE'];
      }

      $this->urlHelper = $urlHelper;
   }

   #[Route('/', name: 'stripe_abonnement_services')]
   public function services(StripeRepository $stripeRepository): Response
   {
      if ($this->getUser()->getAbonnement()) {

         $this->addFlash('warning', 'Vous avez déjà un abonnement actif!');

         return $this->redirectToRoute('vendeur_abonnement');
      }

      $stripe = $stripeRepository->findAll();

      return $this->render('abonnements/services.html.twig', [
         'stripe' => $stripe
      ]);
   }

   #[Route('/success/{priceKey}', name: 'stripe_abonnement_success', methods: ['GET'])]
   public function success(
      AbonnementRepository $abonnementRepo,
      $priceKey,
      StripeRepository $stripeRepository,
      EntityManagerInterface $entityManager
   ): Response {
      $user = $this->getUser();

      $stripeAbonnement = $stripeRepository->findOneBy(['stripeKey' => $priceKey]);

      $findAbonnement = $abonnementRepo->findOneBy(['user' => $user]);

      if (!$findAbonnement) {

         $abonnement = new Abonnement();

         $abonnement->setIsActive(true);
         $abonnement->setIsCanceled(false);
         $abonnement->setUser($user);
         $abonnement->setStripe($stripeAbonnement);
         $abonnement->setStatut(true);
         $entityManager->persist($abonnement);
         $entityManager->flush();
      }

      // Création du portefeuille si l'utilisateur choisis le compte vendeur
      $portefeuille = new Portefeuille();

      if ($user->getCompte() == 'Client') {

         if (!$user->getPortefeuille()) {

            $portefeuille->setSoldeDisponible(0);
            $portefeuille->setSoldeEnCours(0);
            $portefeuille->setVendeur($user);

            $user->setCompte('Vendeur');
            $user->setRoles(['ROLE_VENDEUR']);

            $entityManager->persist($portefeuille);
            $entityManager->persist($user);
            $entityManager->flush();
         }
      }

      return $this->render('abonnements/success.html.twig', [
         'abonnement' => $stripeAbonnement,
      ]);
   }

   #[Route('/cancel', name: 'stripe_abonnement_cancel')]
   public function cancel(): Response
   {
      return $this->render('abonnements/cancel.html.twig', []);
   }

   #[Route('/{priceKey}', name: 'stripe_abonnement_checkout', methods: ['POST'])]
   public function Checkout($priceKey, StripeRepository $abonnementRepo): Response
   {
      $user = $this->getUser();

      $stripeAbonnement = $abonnementRepo->findOneBy(['stripeKey' => $priceKey]);

      \Stripe\Stripe::setApiKey($this->privateKey);

      $websiteDomaine = $this->urlHelper->getAbsoluteUrl('/abonnements');

      $checkout_session = \Stripe\Checkout\Session::create([
         'success_url' => $websiteDomaine . '/success/' . $stripeAbonnement->getStripeKey(),
         'cancel_url' => $websiteDomaine . '/cancel',
         'allow_promotion_codes' => true,
         'payment_method_types' => ['card'],
         'mode' => 'subscription',
         'line_items' => [[
            'price' => $priceKey,
            // For metered billing, do not pass quantity
            'quantity' => 1,
         ]],
         "customer_email" => $user->getEmail()
      ]);

      //dd($checkout_session);

      return $this->render('abonnements/checkout.html.twig', [
         'checkout_session_id' => $checkout_session['id'],
      ]);
   }

   #[Route('/create-portal-session', name: 'stripe_abonnement_create_portal_session')]
   public function createPortalSession()
   {

      //require 'vendor/autoload.php';
      // This is a sample test API key. Sign in to see examples pre-filled with your key.
      \Stripe\Stripe::setApiKey('sk_test_VePHdqKTYQjKNInc7u56JBrQ');

      header('Content-Type: application/json');

      $YOUR_DOMAIN = 'http://localhost:4242/public/success.html';

      try {
         $checkout_session = \Stripe\Checkout\Session::retrieve($_POST['session_id']);
         $return_url = $YOUR_DOMAIN;

         // Authenticate your user.
         $session = \Stripe\BillingPortal\Session::create([
            'customer' => $checkout_session->customer,
            'return_url' => $return_url,
         ]);
         header("HTTP/1.1 303 See Other");
         header("Location: " . $session->url);
      } catch (Error $e) {
         http_response_code(500);
         echo json_encode(['error' => $e->getMessage()]);
      }
   }

   #[Route('/webhooks', name: 'stripe_abonnement_webhooks')]
   public function webhooks()
   {

      // This is a public sample test API key.
      // Don’t submit any personally identifiable information in requests made with this key.
      // Sign in to see your own test API key embedded in code samples.
      \Stripe\Stripe::setApiKey($this->privateKey);

      // Replace this endpoint secret with your endpoint's unique secret
      // If you are testing with the CLI, find the secret by running 'stripe listen'
      // If you are using an endpoint defined with the API or dashboard, look in your webhook settings
      // at https://dashboard.stripe.com/webhooks
      $endpoint_secret = 'whsec_12345';

      $payload = @file_get_contents('php://input');
      $event = null;
      try {
         $event = \Stripe\Event::constructFrom(
            json_decode($payload, true)
         );
      } catch (\UnexpectedValueException $e) {
         // Invalid payload
         echo '⚠️  Webhook error while parsing basic request.';
         http_response_code(400);
         exit();
      }
      // Handle the event
      switch ($event->type) {
         case 'customer.subscription.trial_will_end':
            $subscription = $event->data->object; // contains a \Stripe\Subscription
            // Then define and call a method to handle the trial ending.
            // handleTrialWillEnd($subscription);
            break;
         case 'customer.subscription.created':
            $subscription = $event->data->object; // contains a \Stripe\Subscription
            // Then define and call a method to handle the subscription being created.
            // handleSubscriptionCreated($subscription);
            break;
         case 'customer.subscription.deleted':
            $subscription = $event->data->object; // contains a \Stripe\Subscription
            // Then define and call a method to handle the subscription being deleted.
            // handleSubscriptionDeleted($subscription);
            break;
         case 'customer.subscription.updated':
            $subscription = $event->data->object; // contains a \Stripe\Subscription
            // Then define and call a method to handle the subscription being updated.
            // handleSubscriptionUpdated($subscription);
            break;
         default:
            // Unexpected event type
            echo 'Received unknown event type';
      }
   }

   #[Route('/create-checkout-session', name: 'stripe_abonnement_create_checkout_session')]
   public function createCheckoutSession()
   {

      // This is a public sample test API key.
      // Don’t submit any personally identifiable information in requests made with this key.
      // Sign in to see your own test API key embedded in code samples.
      $stripe = new \Stripe\StripeClient('sk_test_VePHdqKTYQjKNInc7u56JBrQ');

      header('Content-Type: application/json');

      $YOUR_DOMAIN = 'http://localhost:4242/public';

      try {
         $prices = \Stripe\Price::all([
            // retrieve lookup_key from form data POST body
            'lookup_keys' => [$_POST['lookup_key']],
            'expand' => ['data.product']
         ]);

         $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => [[
               'price' => $prices->data[0]->id,
               'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $YOUR_DOMAIN . '/success.html?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
         ]);

         header("HTTP/1.1 303 See Other");
         header("Location: " . $checkout_session->url);
      } catch (Error $e) {
         http_response_code(500);
         echo json_encode(['error' => $e->getMessage()]);
      }
   }
}
