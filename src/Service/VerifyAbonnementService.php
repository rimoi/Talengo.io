<?php

namespace App\Service;

use App\Repository\AbonnementRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VerifyAbonnementService
{
	private $router;
	private $abonnementRepository;

	public function __construct(RouterInterface $router, AbonnementRepository $abonnementRepository){
		$this->router = $router;
		$this->abonnementRepository = $abonnementRepository;
	}

	public function verifyAbonnement($user)
	{
		$target = $this->router->generate('stripe_abonnement_services');

		$abonnement = $this->abonnementRepository->findOneBy(['user' => $user]);

		if ($abonnement === null) {

			return false;
		}

		return true;
	}
}
