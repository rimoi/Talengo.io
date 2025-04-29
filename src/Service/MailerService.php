<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;

class MailerService
{

	public function __construct(private MailerInterface $mailer)
	{
	}

	public function sendMailBecomeSaller($from, $to, $subjet, $template, $user, $message)
	{

		$email = (new TemplatedEmail())
			->from(new Address($from, 'MISSION INFINITY'))
			->to($to)
			->subject($subjet)
			->htmlTemplate($template)
			->context([
				'user' => $user,
				'useremail'  =>  $from,
				'message'   =>  $message
			]);

		return $this->mailer->send($email);
	}

	public function sendMail($from, $to, $subjet, $username, $message, $microservice)
	{


		$email = (new TemplatedEmail())
			->from(new Address($from, 'MISSION INFINITY'))
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

	public function sendCommandMail($from, $to, $subjet, ?string $template = null, $client, $vendeur, $commande)
	{

		if (!$template) {
			$template = 'mails/_default.html.twig';
		}

		$email = (new TemplatedEmail())
			->from(new Address($from, 'MISSION INFINITY'))
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
			->from(new Address($from, 'MISSION INFINITY'))
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
			->from(new Address($from, 'MISSION INFINITY'))
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
