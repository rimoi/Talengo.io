<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stripe/connect', name: 'app_stripe_connect')]
class StripeConnectController extends AbstractController
{
    private $privateKey;

    private $paypalkey;

    public function __construct()
    {
        if ($_ENV['APP_ENV'] === 'dev') {

            $this->privateKey = $_ENV['STRIPE_SECRET_KEY'];
            
        } else {

            $this->privateKey = $_ENV['STRIPE_SECRET_KEY'];
        }
    }

    #[Route('/stripe/connect', name: 'app_stripe_connect')]
    public function index(): Response
    {

        return $this->render('stripe_connect/index.html.twig', [
            'controller_name' => 'StripeConnectController',
        ]);
    }
}
