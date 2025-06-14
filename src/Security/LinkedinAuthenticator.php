<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class LinkedinAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private $sluger;
    private $userPasswordHasher;
    private KernelInterface $kernel;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        SluggerInterface $sluger,
        UserPasswordHasherInterface $userPasswordHasher,
        KernelInterface $kernel,
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->sluger = $sluger;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->kernel = $kernel;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return 'connect_linkedin_check' === $request->attributes->get('_route') && $request->get('service_linkedin') === 'linkedin';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('linkedin');
        $accessToken = $client->getAccessToken();

        $response = $client->getOAuth2Provider()->getAuthenticatedRequest(
            'GET',
            'https://api.linkedin.com/v2/userinfo',
            $accessToken
        );

        $userinfo = $client->getOAuth2Provider()->getParsedResponse($response);

        return new SelfValidatingPassport(
            new UserBadge($accessToken, function () use ($userinfo) {
                $email = $userinfo['email'];

                // 1) have they logged in with Facebook before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($existingUser) {

                    $nameUrl = strtolower($this->sluger->slug($userinfo['family_name'] . '-' . $userinfo['given_name']));

                    $imageUrl = $userinfo['picture'];
                    $imageContents = file_get_contents($imageUrl);

                    if ($imageContents) {
                        $filename = uniqid('linkedin_') . '.jpg';
                        $link = '/uploads/linkedin/' . $filename;

                        $publicPath = $this->kernel->getProjectDir() . '/public' ;

                        $path = $publicPath . $link;

                        file_put_contents($path, $imageContents);

                        if ($existingUser->getReseauAvatar()) {
                            unlink($publicPath.$existingUser->getReseauAvatar());
                        }
                    }

                    $existingUser->setEmail($email)
                        ->setNom($userinfo['family_name'] ?? null)
                        ->setPrenom($userinfo['given_name'] ?? null)
                        ->setNameUrl($nameUrl)
                        ->setLinkedinId($userinfo['sub'] ?? null)
                        ->setReseauAvatar($link)
                        ->setPassword(
                            $this->userPasswordHasher->hashPassword(
                                $existingUser,
                                'azerty'
                            )
                        );

                    $this->entityManager->flush();

                    return $existingUser;
                }

                $email = $userinfo['email'];

                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {

                    $nameUrl = strtolower($this->sluger->slug($userinfo['family_name'] . '-' . $userinfo['given_name']));

                    $imageUrl = $userinfo['picture'];
                    $imageContents = file_get_contents($imageUrl);

                    if ($imageContents) {
                        $filename = uniqid('linkedin_') . '.jpg';
                        $link = '/uploads/linkedin/' . $filename;
                        $path = $this->kernel->getProjectDir() . '/public' . $link;

                        file_put_contents($path, $imageContents);
                    }

                    $user = new User();
                    $user->setEmail($email)
                        ->setNom($userinfo['family_name'] ?? null)
                        ->setPrenom($userinfo['given_name'] ?? null)
                        ->setNameUrl($nameUrl)
                        ->setReseauAvatar($link)
                        ->setCompte('Client')
                        ->setRoles(['ROLE_CLIENT'])
                        ->setLinkedinId($userinfo['sub'] ?? null)
                        ->setIsVerified($userinfo['email_verified'] ?? null)
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
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // change "accueil" to some route in your app
        $targetUrl = $this->router->generate('user_dashboard');

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
