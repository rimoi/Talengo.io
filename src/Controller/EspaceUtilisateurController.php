<?php

namespace App\Controller;

use App\Entity\Portefeuille;
use App\Entity\User;
use App\Form\InformationUserType;
use App\Form\ChangePasswordFormType;
use App\Form\ConfirmType;
use App\Form\CoordonneeType;
use App\Form\EditProfilType;
use App\Form\PositionType;
use App\Form\UserMethodePaymentType;
use App\Repository\CommandeRepository;
use App\Repository\MicroserviceRepository;
use App\Repository\RemboursementRepository;
use App\Repository\RetraitRepository;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-utilisateur')]
class EspaceUtilisateurController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/', name: 'user_profil')]
    public function profil(Request $request): Response
    {
        /*if (empty($this->getUser()->getAdresse())) {
            $this->addFlash('warning', 'Veuillez indiquer votre adresse pour completer votre profil');
            return $this->redirectToRoute('user_edit_adresse');
        }*/
        
        $form = $this->createForm(ConfirmType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('talengo.contact@gmail.com', 'Talengo.io'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email !')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
    
            $this->addFlash('info', 'Vérifez votre boite mail puis cliquez sur le lien "Confirmer mon e-mail"' . 'un mail vous a été envoyé à l\'adresse email:' . $this->getUser()->getEmail());

            return $this->redirectToRoute('user_profil');
        }

        return $this->render('espace_utilisateur/profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/tableau-de-bord', name: 'user_dashboard')]
    public function dashboard(EntityManagerInterface $manager, MicroserviceRepository $microserviceRepository, CommandeRepository $commandeRepository, RetraitRepository $retraitRepository, RemboursementRepository $remboursementRepository, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getCompte() == 'Vendeur') {
            if (!$user->getPortefeuille()) {

                $portefeuille = new Portefeuille();
                $portefeuille->setVendeur($user);
                $portefeuille->setSoldeDisponible(0);
                $portefeuille->setSoldeEnCours(0);

                $manager->persist($portefeuille);
                $manager->flush();
            }
        }
        
        $form = $this->createForm(ConfirmType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('talengo.contact@gmail.com', 'Talengo.io'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email !')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
    
            $this->addFlash('info', 'Vérifez votre boite mail puis cliquez sur le lien "Confirmer mon e-mail"' . 'un mail vous a été envoyé à l\'adresse email:' . $this->getUser()->getEmail());

            return $this->redirectToRoute('user_dashboard');
        }

        return $this->render('espace_utilisateur/dashboard.html.twig', [
            'services' => count($microserviceRepository->findBy(['vendeur' => $user])),
            'commandes' => count($commandeRepository->findBy(['vendeur' => $user, 'statut' => 'En attente'])),
            'retrait' => $retraitRepository->findTotal(['vendeur' => $user]),
            'retraits' => count($retraitRepository->findBy(['vendeur' => $user])),
            'remboursement' => $remboursementRepository->findTotal(['vendeur' => $user]),
            'form' => $form->createView(),
            'remboursements' => count($remboursementRepository->findBy(['vendeur' => $user])),
        ]);
    }

    #[Route('/coordonnees', name: 'user_coordonnees', methods: ['GET', 'POST'])]
    public function coordonnees(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(CoordonneeType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            /** Obliger les studios à joindre une image de couverture */
            //$categorie = $form->get('categorie')->getData()->getName();
            //$imageCouverture = $form->get('couvertureFile')->getData();
            //dd($imageCouverture); dans

            /*if ($categorie == 'Studio' && empty($imageCouverture)) {

                if ($user->getCouverture() == null) {

                    $route = 'user_categorie';
                    $message = "Vous avez choisi l'option studio, veuillez joindre une image de couverture pour votre studio";

                    $this->addFlash('warning', $message);
                    return $this->redirectToRoute($route, [], Response::HTTP_SEE_OTHER);
                }
            }*/

            $entityManager->flush();
            $this->addFlash('success', "Les coordonnées de votre profil ont bien été mise à jour");
            return $this->redirectToRoute('user_categorie', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('espace_utilisateur/coordonnees.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/informations-supplementaires', name: 'user_categorie', methods: ['GET', 'POST'])]
    public function categorie(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(InformationUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->flush();

            $this->addFlash('success', "Vos informations supplémentaires ont bien été mise à jour");
            return $this->redirectToRoute('user_methode_payment', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('espace_utilisateur/categorie.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/methode-paiement', name: 'user_methode_payment', methods: ['GET', 'POST'])]
    public function methodeDePaiement(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserMethodePaymentType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($user->isEndRegister() == null) {
                $user->setEndRegister(true);
            }

            $entityManager->flush();

            $this->addFlash('success', "Votre profil a bien été mise à jour");
            return $this->redirectToRoute('user_profil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('espace_utilisateur/methode_paiement.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modifier-votre-mot-de-passe', name: 'edit_password', methods: ['GET', 'POST'])]
    public function editPassword(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {

        /** @var User $user */
        $user = $this->getUser();

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Encode(hash) the plain password, and set it.
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->flush();

            // The session is cleaned up after the password has been changed.
            //$this->cleanSessionAfterReset();

            $this->addFlash('success', 'Mot de passe modifer avec succès essayer votre nouveau mot de passe');

            return $this->redirectToRoute('app_logout');
        }

        return $this->render('espace_utilisateur/edit_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    #[Route('/modifier-votre-profil', name: 'user_edit_profil', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash('success', "Votre profil a bien été mise à jour");

            return $this->redirectToRoute('user_profil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('espace_utilisateur/edit_profil.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/indiquez-votre-adresse', name: 'user_edit_adresse', methods: ['GET', 'POST'])]
    public function adresse(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(PositionType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash('success', "Votre adresse a bien été mise à jour");

            return $this->redirectToRoute('user_profil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('espace_utilisateur/edit_adresse.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
