<?php

namespace App\Controller\Admin;

use App\Entity\SearchUser;
use App\Entity\User;
use App\Form\AdminEditUserType;
use App\Form\SearchUserType;
use App\Form\UserType;
use App\Repository\MicroserviceRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/users')]
class AdminUsersController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    private $sluger;

    public function __construct(EmailVerifier $emailVerifier, SluggerInterface $sluger)
    {
        $this->emailVerifier = $emailVerifier;
        $this->sluger = $sluger;
    }

    #[Route('/', name: 'app_admin_users_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $search = new SearchUser();
        $search->page = $request->get('page', 1);

        $form = $this->createForm(SearchUserType::class, $search);
        $form->handleRequest($request);

        $users = $userRepository->findSearch($search);

        return $this->render('admin/admin_users/index.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/clients', name: 'app_admin_users_client', methods: ['GET'])]
    public function clients(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $search = new SearchUser();
        $search->page = $request->get('page', 1);

        $form = $this->createForm(SearchUserType::class, $search);
        $form->handleRequest($request);

        $users = $userRepository->findSearch($search);

        return $this->render('admin/admin_users/client.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/vendeurs', name: 'app_admin_users_vendeur', methods: ['GET'])]
    public function vendeurs(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $search = new SearchUser();
        $search->page = $request->get('page', 1);

        $form = $this->createForm(SearchUserType::class, $search);
        $form->handleRequest($request);

        $users = $userRepository->findSearch($search);

        return $this->render('admin/admin_users/vendeur.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administrateurs', name: 'app_admin_users_admin', methods: ['GET'])]
    public function administrateur(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $search = new SearchUser();
        $search->page = $request->get('page', 1);

        $form = $this->createForm(SearchUserType::class, $search);
        $form->handleRequest($request);

        $users = $userRepository->findSearch($search);

        return $this->render('admin/admin_users/admin.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ajouter', name: 'app_admin_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $userNameUrl = $this->sluger->slug(strtolower($user->getNom() . '-' . $user->getPrenom()));

            $user->setNameUrl($userNameUrl);
            $user->setCompte('Vendeur');

            if (in_array('ROLE_VENDEUR', $user->getRoles())) {
                $user->setCompte('Vendeur');
            } elseif (in_array('ROLE_CLIENT', $user->getRoles())) {
                $user->setCompte('Client');
            } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
                $user->setCompte('Administrateur');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('sidilekhalifa1@gmail.com', 'MISSION INFINITY'))
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Compte ' . $user->getCompte() . ' crée avec succès');

            return $this->redirectToRoute('app_admin_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profil/generate/email/{id}', name: 'app_admin_users_generate_email', methods: ['GET'])]
    public function generateEmail(User $user, MailerInterface $mailer, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $password = 'LIN18!15';
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );

        $templateEmail = (new TemplatedEmail())
            ->from(new Address('sidilekhalifa1@gmail.com', 'MISSION INFINITY'))
            ->to(new Address($user->getEmail()))
            ->subject('MISSION C2C - Création de votre compte')
            ->htmlTemplate('mails/_generate_emails.html.twig')
            ->context([
                'identifiant' => $user->getEmail(),
                'password' => $password,
            ]);


        $mailer->send($templateEmail);

        $this->addFlash('success', sprintf('Un email à bien été envoyé au client : `%s`', $user->getEmail()));

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_users_index');
    }

    #[Route('/profil/{nameUrl}', name: 'app_admin_users_show', methods: ['GET'])]
    public function show(User $user, PaginatorInterface $paginator, Request $request, MicroserviceRepository $microserviceRepository): Response
    {
        $services = $paginator->paginate(
            $microserviceRepository->findBy(['vendeur' => $user], ['created' => 'DESC']),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/admin_users/show.html.twig', [
            'user' => $user,
            'services' => $services,
        ]);
    }



    #[Route('/{id}/modifier', name: 'app_admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(AdminEditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // encode the plain password
            if (!empty($form->get('plainPassword')->getData())) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $userNameUrl = $this->sluger->slug(strtolower($user->getNom() . '-' . $user->getPrenom()));

            $user->setNameUrl($userNameUrl);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Compte ' . $user->getCompte() . ' modifié avec succès');

            return $this->redirectToRoute('app_admin_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {

            $this->addFlash('success', 'Compte ' . $user->getCompte() . ' crée avec succès');
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_users_index', [], Response::HTTP_SEE_OTHER);
    }
}
