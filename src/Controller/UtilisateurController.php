<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\UtilisateurType;
use App\Form\UtilisateurAdminType;
use App\Form\RegistrationFormType;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface as Encoder;
use App\Repository\UtilisateurRepository;
use App\Repository\AnnonceRepository;
use App\Repository\NoteRepository;
use Symfony\Component\Security\Core\Security;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/profil", name="profil", methods={"GET","POST"})
     */
    public function edit(Request $request, Encoder $encoder): Response
    {
        if ($this->getUser()) {
            $utilisateur = $this->getUser();

            $form = $this->createForm(UtilisateurType::class, $utilisateur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if($mdp = $form->get("plainPassword")->getData()){
                    $mdp = $encoder->encodePassword($utilisateur, $mdp);
                    $utilisateur->setPassword($mdp);
                }
                $this->getDoctrine()->getManager()->flush();
                return $this->redirectToRoute('profil');
            }
            return $this->render('utilisateur/edit.html.twig', [
                'utilisateur' => $utilisateur,
                'form' => $form->createView(),
            ]);
        }
        return $this->redirectToRoute('home');

    }

    /**
     * @Route("/admin/liste-utilisateurs", name="utilisateur_index", methods={"GET"})
     */
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/{id}/edit", name="utilisateur_edit", methods={"GET","POST"})
     */
    public function editAdmin(Request $request, Utilisateur $utilisateur): Response
    {
        $form = $this->createForm(UtilisateurAdminType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/editAdmin.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="utilisateur_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Utilisateur $utilisateur, Security $security): Response
    {
        if ( !($this->getUser()) ) {
            return $this->redirectToRoute('app_login');
        }
        if ( !( ( $utilisateur->getAuteur()->getId() == $this->getUser()->getId() ) or $security->isGranted('ROLE_ADMIN') ) ) {
            return $this->redirectToRoute('utilisateur_index');
        }
        
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($utilisateur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('utilisateur_index');
    }

    /**
     * @Route("/utilisateur/{id}", name="utilisateur_show", methods={"GET"})
     */
    public function show(Utilisateur $utilisateur, NoteRepository $noterepo, AnnonceRepository $annoncerepo): Response
    {
        $notes = $noterepo->findBy(['utilisateur' => $utilisateur->getId()]);
        $annonces = $annoncerepo->findBy(['auteur' => $utilisateur->getId()]);

        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
            'annonces' => $annonces,
            'notes' => $notes,
        ]);
    }
}
