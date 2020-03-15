<?php

namespace App\Controller;

use App\Entity\{Card, Faction};
use App\Repository\CardRepository;
use App\Form\CardCreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/card", name="card")
 */
class CardController extends AbstractController
{
    static protected $route = 'card';

    /**
     * @Route("/", name="List")
     */
    public function index(CardRepository $cardRepository)
    {
        $card = $cardRepository->findAll();

        return $this->render('generic/list.html.twig', [
            'title' => 'Liste des cartes',
            'path' => self::$route,
            'table' => $card,
            'isNotGeneric' => true,
            'customList' => 'card/cardList',
            'pathImg' => $this->getParameter('imgUploadPublic') . '/'
        ]);
    }

    /**
     * @Route("/create", name="Create")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request)
    {
        $newCard = new Card();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CardCreateType::class, $newCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('imageName')->getData();

            if ($file) {
                $newFileName =uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move( $this->getParameter('imgUpload'), $newFileName);
                } catch (FileException $e) {
                    print_r($e);
                }

                $newCard->setImageName($newFileName);
            }

            $newCard->setAuthor($this->getUser());

            $em->persist($newCard);
            $em->flush();

            $this->addFlash('notice', "La carte a bien été ajouté");

            return $this->redirectToRoute(self::$route . 'List');
        }

        return $this->render('generic/form.html.twig', [
            'title' => 'Créer une carte',
            'path' => self::$route,
            'form' => $form->createView(),
            'action' => self::$route . 'Create',
            'actionHasParameter' => false,
            'id' => ''
        ]);
    }

    /**
     * @Route("/edit/{id}", name="Edit")
     * @ParamConverter("card", options={"mapping"={"id"="id"}})
     */
    public function edit(Request $request, Card $card)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(CardCreateType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('imageName')->getData();

            if ($file) {
                $newFileName =uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move( $this->getParameter('imgUpload'), $newFileName);
                } catch (FileException $e) {
                    print_r($e);
                }

                $card->setImageName($newFileName);
            }

            $card->setAuthorCard($this->getUser());

            $em->persist($card);
            $em->flush();

            $this->addFlash('notice', "La carte a bien été mis à jour");

            return $this->redirectToRoute(self::$route . 'List');
        }

        return $this->render('generic/form.html.twig', [
            'title' => 'Liste des cartes',
            'path' => self::$route,
            'form' => $form->createView(),
            'action' => self::$route . 'Edit',
            'actionHasParameter' => true,
            'id' => $card->getId()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="Delete")
     * @ParamConverter("card", options={"mapping"={"id"="id"}})
     */
    public function delete(Card $card)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($card);
        $em->flush();

        $this->addFlash('notice', "La carte a bien été supprimé");

        return $this->redirectToRoute(self::$route . 'List');
    }
}
