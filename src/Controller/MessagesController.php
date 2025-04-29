<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Mercure\PublisherInterface;
//use Symfony\Component\Mercure\Update;
//use Symfony\Component\Mercure\HubInterface;

#[Route('/messages')]
class MessagesController extends AbstractController
{
    #[Route('/', name: 'send_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        ConversationRepository $conversationRepository, 
    	SerializerInterface $serializer, 
        EntityManagerInterface $em,
        /*HubInterface $hub*/): JsonResponse
    {
        $data = \json_decode($request->getContent(), true); // On récupère les data postées et on les déserialize

        if (empty($content = $data['content'])) {
            throw new AccessDeniedHttpException('No data sent');
        }

        $conversation = $conversationRepository->findOneBy([
            'id' => $data['conversation'] // On cherche à savoir de quel channel provient le message
        ]);

        if (!$conversation) {
            throw new AccessDeniedHttpException('Message have to be sent on a specific conversation');
        }

        $destinataire = null;

        if($this->getUser()->getId() == $conversation->getUser1()->getId()){
            $destinataire = $conversation->getUser2();
        }else{
            $destinataire = $conversation->getUser1();
        }

        $message = new Message(); // Après validation, on crée le nouveau message
        $message->setContenu($content);
        $message->setConversation($conversation);
        $message->setAuteur($this->getUser()); // On lui attribue comme auteur l'utilisateur courant
        $message->setDestinataire($destinataire);
        $message->setLu(false);

        $em->persist($message);
        $em->flush();

        $conversation->setSender($this->getUser());
        $conversation->setCreated(new \DateTimeImmutable());
        $conversation->setLastMessage($message);

        $em->flush();

        $jsonMessage = $serializer->serialize($message, 'json', [
            'groups' => ['message'] // On serialize la réponse avant de la renvoyer
        ]);

        // Finalité
        /*$update = new Update([
                sprintf('https://127.0.0.1:8000/conversations/%s', $conversation->getId()),
                sprintf('https://127.0.0.1:8000/conversations-%s', $destinataire->getId()),
                sprintf('https://127.0.0.1:8000/conversations-%s', $this->getUser()->getId())
            ],
            $jsonMessage
        );*/

        // Sync, or async (Doctrine, RabbitMQ, Kafka...)
        //$hub->publish($update);

        return new JsonResponse( // Enfin, on retourne la réponse
            $jsonMessage,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/publish', name: 'publish', methods: ['POST', 'GET'])]
    public function publish(/*HubInterface $hub*/): Response
    {
        /*$update = new Update(
            'https://127.0.0.1:8000/conversations/12',
            json_encode(['status' => 'Published!'])
        );

        // Sync, or async (Doctrine, RabbitMQ, Kafka...)
        $hub->publish($update);*/

        return new Response('published!');
    }
}
