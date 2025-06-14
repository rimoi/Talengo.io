<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private const SCOPES = [
        'google' => [],
        'facebook' => [],
        'linkedin' => ['openid', 'profile', 'email'],
    ];


    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('accueil');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/facebook/connect", name="facebook_connect")
     */
    public function connect(ClientRegistry $clientRegistry){
        /** @var FacebookClient $client */
        $client = $clientRegistry->getClient('facebook');
        return $client->redirect([
            'public_profile', 'email'
        ]);
    }

    /**
     * @Route("/google/connect", name="google_connect")
     */
    public function googleConnect(ClientRegistry $clientRegistry){
        /** @var GoogleClient $client */
        $client = $clientRegistry->getClient('google');
        return $client->redirect([
            'profile', 'email'
        ]);
    }

    #[Route('/linkedin/connect', name: 'connect_linkedin')]
    public function googleLinkedin(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('linkedin')
            ->redirect(['email', 'profile', 'w_member_social', 'openid']); // Scopes demandés
    }
    #[Route('/oauth/linkedin/check', name: 'connect_linkedin_check')]
    public function checkLinkedin( ClientRegistry $clientRegistry)
    {
        $client = $clientRegistry->getClient('linkedin');
        $accessToken = $client->getAccessToken(); // ou récupère depuis la session

        $response = $client->getOAuth2Provider()->getAuthenticatedRequest(
            'GET',
            'https://api.linkedin.com/v2/userinfo',
            $accessToken
        );

        $userinfo = $client->getOAuth2Provider()->getParsedResponse($response);

        dump($userinfo); die;
        // Traitez les données utilisateur ($user->getFirstName(), etc.)
        // Exemple : stockez en session ou BDD
        return $this->redirectToRoute('accueil');
    }



    /**
     * @Route("/oauth/connect/{service}", name="auth_oauth_connect")
     */
    public function connectService(string $service, ClientRegistry $clientRegistry): Response
    {

        if (! in_array($service, array_keys(self::SCOPES), true)) {
            throw $this->createNotFoundException();
        }

        return $clientRegistry->getClient($service)->redirect(self::SCOPES[$service]);
    }

    /**
     * @Route("/oauth/check/{service_google}", name="auth_oauth_check")
     */
    public function check(): Response
    {
        return new Response(status: Response::HTTP_OK);
    }

    /**
     * @Route("/instagram/connect", name="instagram_connect")
     */
    public function instagramConnect(ClientRegistry $clientRegistry){
        /** @var InstagramClient $client */
        $client = $clientRegistry->getClient('instagram');
        return $client->redirect([
            'user_profile', 'user_media'
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
