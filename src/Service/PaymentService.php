<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
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

   public function startStripePayment($montant, $request, $intent)
   {
      // Stripe payment
      if ($montant > 0) {

         // Instanciation Stripe
         \Stripe\Stripe::setApiKey($this->privateKey);

         $intent = \Stripe\PaymentIntent::create([
            'amount'    =>  $montant * 100,
            'currency'  =>  'eur',
            'payment_method_types'  =>  ['card']
         ]);
         // Traitement du formulaire Stripe

         if ($request->getMethod() === "POST") {

            if ($intent['status'] === "requires_payment_method") {
               // TODO

            }
         }

         return $intent;
      } else {
         //dd('aucun prix');
      }
   }

   public function startPaypalPayment($montant, $serviceName)
   {
      // Calcul des taxes
      $tva = 0.2;
      $montantTva = $montant * $tva;

      // Frais bancaire
      $frais = 0.029;

      $somme = ($montantTva + $montant) * $frais;

      $order = [
         'purchase_units' => [[
            'description'    => 'MISSION C2C achats de prestation',
            'items'   =>  [
               'name'  =>  $serviceName,
               'quatity'   =>  1,
               'unit_amount'   =>  [
                  'value'     =>  $somme * 100,
                  'currency_code' =>  'EUR',
               ],
            ],

            'amount'  =>  [
               'currency_code' =>  'EUR',
               'value'         =>  $somme * 100,
            ]
         ]]
      ];
   }
}
