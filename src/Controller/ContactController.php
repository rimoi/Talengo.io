<?php

namespace App\Controller;

use App\Form\Model\ContactType;
use App\Model\Contact;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        MailerService $mailerService
    ): Response
    {
        $contact =  new Contact();

        if ($this->getUser()) {
            $contact->setEmail($this->getUser()->getEmail());
        }

        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', "Merci pour votre message ! Nous avons bien reçu votre demande et reviendrons vers vous sous 24 à 48 heures. L'équipe Talengo.io");

            $mailerService->sendMailBecomeSaller(
                'talengo.contact@gmail.com',
                'talengo.contact@gmail.com',
                $contact->getName(),
                'mails/admin/_contact.html.twig',
                $contact,
                $this->getUser()
            );

            return $this->redirectToRoute('app_contact');
        }

        return $this->renderForm('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
