<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use App\Repository\NoteRepository;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/annonce")
 */
class AnnonceController extends AbstractController
{
    /**
     * @Route("/", name="annonce_index", methods={"GET"})
     */
    public function index(AnnonceRepository $annonceRepository): Response
    {
        return $this->render('annonce/index.html.twig', [
            'annonces' => $annonceRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="annonce_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if ( !($this->getUser()) ) {
            return $this->redirectToRoute('app_login');
        }
        
        $annonce = new Annonce();
        $utilisateur = $this->getUser();
        $annonce->setAuteur($utilisateur);

        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $annonce->setCreation(new \DateTime("now"));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($annonce);
            $entityManager->flush();

            return $this->redirectToRoute('annonce_index');
        }

        return $this->render('annonce/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="annonce_show", methods={"GET"})
     */
    public function show(Annonce $annonce, NoteRepository $noterepo, CommentaireRepository $commentrepo): Response
    {
        $notes = $noterepo->findBy(['utilisateur' => $annonce->getAuteur()->getId()]);
        $comments = $commentrepo->findBy(['annonce' => $annonce->getId()]);

        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
            'comments' => $comments,
            'notes' => $notes,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="annonce_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Annonce $annonce, Security $security): Response
    {
        if ( !($this->getUser()) ) {
            return $this->redirectToRoute('app_login');
        }
        if ( !( ( $annonce->getAuteur()->getId() == $this->getUser()->getId() ) or $security->isGranted('ROLE_MOD') ) ) {
            return $this->redirectToRoute('note_index');
        }
        
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('annonce_index');
        }

        return $this->render('annonce/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="annonce_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Annonce $annonce, Security $security): Response
    {
        if ( !($this->getUser()) ) {
            return $this->redirectToRoute('app_login');
        }
        if ( !( ( $annonce->getAuteur()->getId() == $this->getUser()->getId() ) or $security->isGranted('ROLE_MOD') ) ) {
            return $this->redirectToRoute('note_index');
        }
        
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($annonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('annonce_index');
    }
}
