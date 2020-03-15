<?php

namespace App\Controller;

use App\Entity\{DeckCard, Deck, Card};
use App\Repository\DeckCardRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/deckCard", name="deckCard")
 */
class DeckCardController extends AbstractController
{

    static protected $route = 'deckCard';

    /**
     * @Route("/create/{deck}/{card}", name="Create")
     * @ParamConverter("deck", options={"mapping"={"deck"="id"}})
     * @ParamConverter("card", options={"mapping"={"card"="id"}})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, Deck $deck, Card $card, DeckCardRepository $deckCardRepository)
    {
        $em = $this->getDoctrine()->getManager();

        $deckCard = $deckCardRepository->findBy([
            'deck' => $deck->getId(),
            'card' => $card->getId()
        ]);

        if ($deckCard) {

            $deckCard[0]->setQuantity($deckCard[0]->getQuantity() + 1);

            $em->persist($deckCard[0]);
            $em->flush();

        } else {

            $newDeckCard = new DeckCard();

            $newDeckCard->setDeck($deck);
            $newDeckCard->setCard($card);
            $newDeckCard->setQuantity(1);

            $em->persist($newDeckCard);
            $em->flush();
        }

        $this->addFlash('notice', "La carte a bien été ajoutée au deck");

        return $this->redirectToRoute('deckEdit', ['id' => $deck->getId()]);
    }

    /**
     * @Route("/delete/{deck}/{card}", name="Delete")
     * @ParamConverter("deck", options={"mapping"={"deck"="id"}})
     * @ParamConverter("card", options={"mapping"={"card"="id"}})
     */
    public function delete(DeckCard $deckCard, Deck $deck, Card $card, DeckCardRepository $deckCardRepository)
    {
        $em = $this->getDoctrine()->getManager();

        $deckCard = $deckCardRepository->findBy([
            'deck' => $deck->getId(),
            'card' => $card->getId()
        ]);

        $deckCardQuantity = $deckCard[0]->getQuantity();

        if (1 === $deckCardQuantity) {

            $em->remove($deckCard[0]);

        } else {

            $deckCard[0]->setQuantity($deckCardQuantity - 1);

            $em->persist($deckCard[0]);
        }

        $em->flush();

        $this->addFlash('notice', "La carte a bien été supprimé du deck");

/*         return $this->render('deck_card/index.html.twig', [
            'test' => $deckCardQuantity
        ]); */

        return $this->redirectToRoute('deckEdit', ['id' => $deck->getId()]);
    }
}
