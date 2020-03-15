<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Repository\{DeckRepository, DeckCardRepository, CardRepository};
use App\Form\DeckCreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

    /**
     * @Route("/deck", name="deck")
     */
class DeckController extends AbstractController
{

    static protected $route = 'deck';

    /**
     * @Route("/", name="List")
     */
    public function index(DeckRepository $deckRepository)
    {
        $deck = $deckRepository->findBy([
            'authorDeck' => $this->getUser(),
        ]);

        return $this->render('generic/list.html.twig', [
            'title' => 'Liste des decks',
            'path' => self::$route,
            'table' => $deck,
            'isNotGeneric' => false,
            'customList' => ''
        ]);
    }

    /**
     * @Route("/create", name="Create")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request)
    {
        $newDeck = new Deck();
        $em = $this->getDoctrine()->getManager();

        $newDeck->setName('Nouveau deck');
        $newDeck->setAuthorDeck($this->getUser());

        $em->persist($newDeck);
        $em->flush();

        $this->addFlash('notice', "Le deck a bien été crée");

        return $this->redirectToRoute(self::$route . 'Edit', ['id' => $newDeck->getId()]);
    }

    /**
     * @Route("/edit/{id}", name="Edit")
     * @ParamConverter("deck", options={"mapping"={"id"="id"}})
     */
    public function edit(Request $request, Deck $deck, CardRepository $cardRepository, DeckCardRepository $deckCardRepository)
    {
        $em = $this->getDoctrine()->getManager();

        $card = $cardRepository->findBy(['authorCard' => $this->getUser()]);
        $deckCard = $deckCardRepository->findBy(['deck' => $deck]);

        $form = $this->createForm(DeckCreateType::class, $deck);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($deck);
            $em->flush();

            $this->addFlash('notice', "Le deck a bien été mis à jour");

            return $this->redirectToRoute(self::$route . 'List');
        }

        return $this->render('deck/deckEdit.html.twig', [
            'title' => 'Editer un deck',
            'path' => self::$route,
            'form' => $form->createView(),
            'action' => self::$route . 'Edit',
            'id' => $deck->getId(),
            'pathImg' => $this->getParameter('imgUploadPublic') . '/',
            'deckCard' => $deckCard,
            'card' => $card,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="Delete")
     * @ParamConverter("deck", options={"mapping"={"id"="id"}})
     */
    public function delete(Deck $deck)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($deck);
        $em->flush();

        $this->addFlash('notice', "Le deck a bien été supprimé");

        return $this->redirectToRoute(self::$route . 'List');
    }
}
