<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Facebook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class FacebookAuthController extends AbstractController
{

    private $provider;

    private $sluger;

    public function __construct(SluggerInterface $sluger)
    {
        $this->provider = new Facebook([
            'clientId'          => $_ENV['FACEBOOK_ID'],
            'clientSecret'      => $_ENV['FACEBOOK_SECRET'],
            'redirectUri'       => $_ENV['FACEBOOK_CALLBACK'],
            'graphApiVersion'   => 'v15.0',
        ]);

        $this->sluger = $sluger;
    }

    #[Route('/facebook-home', name: 'facebook_home')]
    public function index(): Response
    {
        return $this->render('facebook/index.html.twig', []);
    }

    #[Route('/facebook-login', name: 'facebook_login', methods: ['GET', 'POST'])]
    public function facLogin(): Response
    {
        $helper = $this->provider->getAuthorizationUrl();

        return $this->redirect($helper);
    }

    #[Route('/fcb-callback', name: 'facebook_callback', methods: ['GET'])]
    public function facCallback(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        // Recupération du token (jeton d'accès, o)
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        try {
            // Obtenons maintenant les détails de l'utilisateur
            $faceBookUser = $this->provider->getResourceOwner($token);
            $faceBookUser = $faceBookUser->toArray();
            $email = $faceBookUser['email'];
            $name = $faceBookUser['name'];
            $nom = $faceBookUser['last_name'];
            $prenom = $faceBookUser['first_name'];
            $avatar = $faceBookUser['picture_url'];

            // Vérifier si l'utilisateur existe dans la BD
            $user = $userRepository->findOneBy(['email' => $email]);
            $nameUrl = strtolower($this->sluger->slug($nom . '-' . $prenom));

            if ($user) {

                $user->setNom($nom)
                    ->setPrenom($prenom)
                    ->setNameUrl($nameUrl)
                    ->setReseauAvatar($avatar)
                    ->setEmail($email)
                    ->setCompte('Client')
                    ->setRoles(['ROLE_CLIENT'])
                    ->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            'azerty'
                        )
                    );
                $entityManager->flush();

                $statut = " mis à jour";

            } else {
                $user = new User();

                $user->setNom($nom)
                    ->setPrenom($prenom)
                    ->setNameUrl($nameUrl)
                    ->setEmail($email)
                    ->setReseauAvatar($avatar)
                    ->setCompte('Client')
                    ->setRoles(['ROLE_CLIENT'])
                    ->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            'azerty'
                        )
                    );
                $entityManager->persist($user);
                $entityManager->flush();

                $statut = " crée";
            }

        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

        return $this->render('facebook/login.html.twig', []);
    }
}
