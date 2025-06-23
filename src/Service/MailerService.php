<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\User;
use App\Model\Contact;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;

class MailerService
{

	public function __construct(private MailerInterface $mailer)
	{
	}

	public function sendMailBecomeSaller($from, $to, $subjet, $template, Contact $contact, ?User $user)
	{

		$email = (new TemplatedEmail())
			->from(new Address($from, 'Talengo.io'))
			->to($to)
			->subject($subjet)
			->htmlTemplate($template)
			->context([
				'user' => $user,
				'contact'   =>  $contact
			]);

		return $this->mailer->send($email);
	}

	public function sendMail($from, $to, $subjet, $username, $message, $microservice)
	{


		$email = (new TemplatedEmail())
			->from(new Address($from, 'Talengo.io'))
			->to($to)
			->subject($subjet)
			->htmlTemplate('mails/_default.html.twig')
			->context([
				'user' => $username,
				'useremail'  =>  $from,
				'message'   =>  $message,
				'microservice'   =>  $microservice
			]);

		return $this->mailer->send($email);
	}

	public function sendCommandMail($from, $to, $subjet, ?string $template = null, ?User $client = null, ?User $vendeur = null, ?Commande $commande = null)
	{

		if (!$template) {
			$template = 'mails/_default.html.twig';
		}

		$email = (new TemplatedEmail())
			->from(new Address($from, 'Talengo.io'))
			->to($to)
			->subject($subjet)
			->htmlTemplate($template)
			->context([
				'client' => $client,
				'vendeur' => $vendeur,
				'commande'   =>  $commande
			]);

		return $this->mailer->send($email);
	}

	public function sendDemandeMail($from, $to, $subjet, $template, $vendeur, $retrait)
	{

		if (!isset($template)) {
			$template = 'mails/_default.html.twig';
		}

		$email = (new TemplatedEmail())
			->from(new Address($from, 'Talengo.io'))
			->to($to)
			->subject($subjet)
			->htmlTemplate($template)
			->context([
				'vendeur' => $vendeur,
				'demande'   =>  $retrait
			]);

		return $this->mailer->send($email);
	}

	public function sendPackMail($from, $to, $subjet, $template, $client, $commande)
	{

		$email = (new TemplatedEmail())
			->from(new Address($from, 'Talengo.io'))
			->to($to)
			->subject($subjet)
			->htmlTemplate($template)
			->context([
				'client' => $client,
				'commande'   =>  $commande
			]);

		return $this->mailer->send($email);
	}
}
