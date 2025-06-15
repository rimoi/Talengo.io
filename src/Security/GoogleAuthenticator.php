<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\String\Slugger\SluggerInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    use TargetPathTrait;

   private $clientRegistry;
   private $entityManager;
   private $router;
   private $sluger;
   private $userPasswordHasher;

   public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router, SluggerInterface $sluger, UserPasswordHasherInterface $userPasswordHasher)
   {
      $this->clientRegistry = $clientRegistry;
      $this->entityManager = $entityManager;
      $this->router = $router;
      $this->sluger = $sluger;
      $this->userPasswordHasher = $userPasswordHasher;
   }

   public function supports(Request $request): ?bool
   {
      return 'auth_oauth_check' === $request->attributes->get('_route') && $request->get('service_google') === 'google';
   }

   public function authenticate(Request $request): Passport
   {
      $client = $this->clientRegistry->getClient('google');
      $accessToken = $this->fetchAccessToken($client);

      return new SelfValidatingPassport(
         userBadge: new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {

            /** @var GoogleUser $googleUser */
            $googleUser = $client->fetchUserFromToken($accessToken);

            $email = $googleUser->getEmail();

            // 1) have they logged in with Google before? Easy!
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($existingUser) {

               $nameUrl = strtolower($this->sluger->slug($existingUser->getNom() . ' ' . $existingUser->getPrenom()));

               $existingUser->setEmail($googleUser->getEmail())
                  ->setNom($googleUser->getName())
                  ->setPrenom($googleUser->getFirstName())
                  ->setNameUrl($nameUrl)
                  ->setReseauAvatar($googleUser->getAvatar())
                  ->setPassword(
                     $this->userPasswordHasher->hashPassword(
                        $existingUser,
                        'azerty'
                     )
                  );
               $this->entityManager->flush();

               return $existingUser;
            }

            // 2) do we have a matching user by email?
            $user = $this->entityManager->getRepository(User::class)
               ->findOneBy(['email' => $email]);

            // 3) Maybe you just want to "register" them by creating
            // a User object
            if (!$user) {

               $nameUrl = strtolower($this->sluger->slug($googleUser->getFirstName() . '-' . $googleUser->getLastName()));

               $user = new User();
               $user->setEmail($googleUser->getEmail())
                  ->setNom($googleUser->getLastName())
                  ->setPrenom($googleUser->getFirstName())
                  ->setNameUrl($nameUrl)
                  ->setReseauAvatar($googleUser->getAvatar())
                  ->setCompte('Client')
                  ->setRoles(['ROLE_CLIENT'])
                  ->setGoogleId($googleUser->getId())
                  ->setIsVerified($googleUser->toArray()['email_verified'] ?? false)
                  ->setPassword(
                     $this->userPasswordHasher->hashPassword(
                        $user,
                        'azerty'
                     )
                  );
               $this->entityManager->persist($user);
               $this->entityManager->flush();
            }

            return $user;
         }),
          badges: [
              new RememberMeBadge(),
          ]
      );
   }

   public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
   {
       $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

       if ($targetPath) {
           return new RedirectResponse($targetPath);
       }

      // change "accueil" to some route in your app
      $targetUrl = $this->router->generate('list');

      return new RedirectResponse($targetUrl);

   }

   public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
   {
      $message = strtr($exception->getMessageKey(), $exception->getMessageData());

      return new Response($message, Response::HTTP_FORBIDDEN);
   }

   /**
    * Called when authentication is needed, but it's not sent.
    * This redirects to the 'login'.
    */
   public function start(Request $request, AuthenticationException $authException = null): Response
   {
      return new RedirectResponse(
         $this->router->generate('app_login'), // might be the site, where users choose their oauth provider
         Response::HTTP_TEMPORARY_REDIRECT
      );
   }
}
