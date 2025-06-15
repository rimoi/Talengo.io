<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Form\ChatMessageType;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/conversations')]
class ConversationsController extends AbstractController
{
    #[Route('/', name: 'conversations')]
    public function index(ConversationRepository $conversationsRepository): Response
    {
        return $this->render('conversations/index.html.twig', [
            'conversation' => null,
            'conversations' => $conversationsRepository->findByParticipation($this->getUser()),
        ]);
    }

    #[Route('/{id}', name: 'conversations_show', methods: ['GET', 'POST'])]
    public function chat(
        Conversation $conversation,
        ConversationRepository $conversationsRepository,
        Request $request,
        MessageRepository $messageRepository,
        EntityManagerInterface $entityManager
    ): Response {

        /** @var User */
        $user = $this->getUser();

        //*verification des participants
        $participants = [$conversation->getUser1(), $conversation->getUser2()];

        if (!in_array($user, $participants)) {

            return $this->redirectToRoute('conversations', [], Response::HTTP_SEE_OTHER);
        }

        if ($conversation->getLastMessage()->getDestinataire()->getId() == $user->getId() && $conversation->getLastMessage()->getLu() == 0) {

            $message = $messageRepository->find($conversation->getLastMessage()->getId());
            $message->setLu(true);
            $entityManager->flush();
            
        }

        $messages = $messageRepository->findBy([
            'conversation' => $conversation
        ], ['created' => 'ASC']);

        $usersconversations = $conversationsRepository->findByParticipation($user);

        // Test de message
        $message = new Message();
        $form = $this->createForm(ChatMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $destinataire = null;

            if ($user->getId() == $conversation->getUser1()->getId()) {
                $destinataire = $conversation->getUser2();
            } else {
                $destinataire = $conversation->getUser1();
            }

            $message->setConversation($conversation);
            $message->setAuteur($user); // On lui attribue comme auteur l'utilisateur courant
            $message->setDestinataire($destinataire);
            $message->setLu(false);

            $entityManager->persist($message);
            $entityManager->flush();

            $conversation->setSender($this->getUser());
            $conversation->setCreated(new \DateTimeImmutable());
            $conversation->setLastMessage($message);
    
            $entityManager->flush();


            return $this->redirectToRoute('conversations_show', [
                'id' => $conversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }


        return $this->render('conversations/chat.html.twig', [
            'conversation' => $conversation,
            'conversations' => $usersconversations,
            'messages' => $messages,
            'microservice' => $conversation->getMicroservice(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/nouveau-message/{vendeurNameUrl}', name: 'conversations_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        $vendeurNameUrl,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ConversationRepository $conversationsRepository
    ): Response {

        $vendeur = $userRepository->findOneBy([
            'nameUrl' => $vendeurNameUrl
        ]);

        $user = $this->getUser();

        $findconversation = $conversationsRepository->findOneBy([
            'user1' => $user,
            'user2' => $vendeur
        ]);

        if ($findconversation) {
            return $this->redirectToRoute('conversations_show', [
                'id' => $findconversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        $conversation = new Conversation();

        $message = new Message();
        $messageParDefaut = "Bonjour, je suis intéressé par votre service. Avant de passer commande, êtes-vous disponible pour me renseigner ?";

        $message->setContenu($messageParDefaut);
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $conversation->setUser1($user);
            $conversation->setUser2($vendeur);
            $conversation->setSender($user);
            $conversation->setTerminee(false);
            $entityManager->persist($conversation);

            $message->setConversation($conversation);
            $message->setAuteur($user);
            $message->setDestinataire($vendeur);
            $message->setLu(false);
            $entityManager->persist($message);

            $entityManager->flush();

            $conversation->setLastMessage($message);

            $entityManager->flush();

            return $this->redirectToRoute('conversations_show', [
                'id' => $conversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conversations/new.html.twig', [
            'form' => $form,
            'vendeur' => $vendeur,
            'microservice' => false
        ]);
    }

    #[Route('/microservice/chat/{vendeurNameUrl}/{microserviceId}', name: 'microservice_conversations', methods: ['GET', 'POST'])]
    public function addWithmicroservice(
        Request $request,
        $vendeurNameUrl,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MicroserviceRepository $microserviceRepo,
        $microserviceId,
        ConversationRepository $conversationsRepository
    ): Response {

        $microservice = $microserviceRepo->find($microserviceId);

        $vendeur = $microservice->getVendeur();

        $user = $this->getUser();

        if (!$microservice) {
            throw $this->createNotFoundException('microservice is missing!');
        }

        $findconversation = $conversationsRepository->findOneBy([
            'user1' => $user,
            'user2' => $vendeur,
            'microservice' => $microservice
        ]);

        if ($findconversation) {
            return $this->redirectToRoute('conversations_show', [
                'id' => $findconversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        $conversation = new Conversation();

        $message = new Message();
        
        $messageParDefaut = "Bonjour, je suis intéressé par votre service. Avant de passer commande, êtes-vous disponible pour me renseigner ?";

        $message->setContenu($messageParDefaut);
        
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $conversation->setUser1($this->getUser());
            $conversation->setUser2($vendeur);
            $conversation->setTerminee(false);
            $conversation->setMicroservice($microservice);
            $entityManager->persist($conversation);

            $message->setConversation($conversation);
            $message->setAuteur($this->getUser());
            $message->setDestinataire($vendeur);
            $message->setLu(false);
            $entityManager->persist($message);

            $entityManager->flush();

            $conversation->setLastMessage($message);

            $entityManager->flush();

            return $this->redirectToRoute('conversations_show', [
                'id' => $conversation->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conversations/new.html.twig', [
            'form' => $form,
            'vendeur' => $vendeur,
            'microservice' => $microservice
        ]);
    }

    #[Route('/{id}', name: 'conversations_terminee', methods: ['POST'])]
    public function terminee(Request $request, Conversation $conversation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('terminee' . $conversation->getId(), $request->request->get('_token'))) {
            $conversation->setTerminee(true);
            $entityManager->persist($conversation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('conversations', [], Response::HTTP_SEE_OTHER);
    }
}
