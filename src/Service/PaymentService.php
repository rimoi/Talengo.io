<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{

   private $privateKey;

   private $paypalkey;

   public function __construct()
   {
     $this->privateKey = $_ENV['STRIPE_SECRET_KEY'];

     $this->paypalkey = $_ENV['PAYPAL_SECRET'];
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
            'description'    => 'Talengo.io achats de prestation',
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


    public function hydrateInfo(Request $request, User $user): void
    {
        if ($request->get('nom')) {
            $user->setNom($request->get('nom'));
        }
        if ($request->get('prenom')) {
            $user->setPrenom($request->get('prenom'));
        }
        if ($request->get('adresse')) {
            $user->setAdresse($request->get('adresse'));
        }
        if ($request->get('ville')) {
            $user->setVille($request->get('ville'));
        }
        if ($request->get('code_postal')) {
            $user->setCodePostal($request->get('code_postal'));
        }
        if ($request->get('denomination')) {
            $user->setDenomination($request->get('denomination'));
        }
        if ($request->get('numero_tva')) {
            $user->setNumeroTVA($request->get('numero_tva'));
        }
    }
}
