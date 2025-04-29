<?php

namespace App\Controller;

use App\Entity\CommandeMessage;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use App\Repository\CommandeMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\HubInterface;

#[Route('/commandes-messages')]
class CommandesMessageController extends AbstractController
{
    #[Route('/', name: 'send_commande_message', methods: ['POST', 'GET'])]
    public function sendCommandeMessage(
        CommandeRepository $commandeRepository,
        Request $request, 
    	SerializerInterface $serializer, 
        EntityManagerInterface $em,
        HubInterface $hub): JsonResponse
    {
        $data = \json_decode($request->getContent(), true); // On récupère les data postées et on les déserialize

        if (empty($content = $data['content'])) {
            throw new AccessDeniedHttpException('No data sent');
        }

        $commande = $commandeRepository->findOneBy([
            'id' => $data['commande'] // On cherche à savoir de quel channel provient le message
        ]);

        if (!$commande) {
            throw new AccessDeniedHttpException('Message have to be sent on a specific commande');
        }

        $destinataire = null;

        if($this->getUser()->getId() == $commande->getVendeur()->getId()){
            $destinataire = $commande->getClient();
        }else{
            $destinataire = $commande->getVendeur();
        }
        
        $commande->setDestinataire($destinataire);
        $commande->setLu(false);

        $message = new CommandeMessage();
        $message->setContenu($content);
        $message->setCommande($commande);
        $message->setUser($this->getUser());
        $message->setLu(false);
        $em->persist($message);
        $em->flush();

        $jsonMessage = $serializer->serialize($message, 'json', [
            'groups' => ['message'] // On serialize la réponse avant de la renvoyer
        ]);

        // Finalité
        $update = new Update(
            sprintf('https://127.0.0.1:8000/commandes/%s', $commande->getId()),
            $jsonMessage,
        );

        // Sync, or async (Doctrine, RabbitMQ, Kafka...)
        $hub->publish($update);

        return new JsonResponse( // Enfin, on retourne la réponse
            $jsonMessage,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/publish', name: 'commande_publish', methods: ['POST', 'GET'])]
    public function publish(HubInterface $hub): Response
    {
        $update = new Update(
            'https://127.0.0.1:8000/commandes/12',
            json_encode(['status' => 'Published!'])
        );

        // Sync, or async (Doctrine, RabbitMQ, Kafka...)
        $hub->publish($update);

        return new Response('published!');
    }
}
