<?php

namespace App\Controller;

use App\Entity\Faction;
use App\Repository\FactionRepository;
use App\Form\FactionCreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/faction", name="faction")
 */
class FactionController extends AbstractController
{
    static protected $route = 'faction';

    /**
     * @Route("/", name="List")
     */
    public function index(FactionRepository $factionRepository)
    {
        $faction = $factionRepository->findBy([
            'authorFaction' => $this->getUser(),
        ]);

        return $this->render('generic/list.html.twig', [
            'title' => 'Liste des factions',
            'path' => self::$route,
            'table' => $faction,
            'isNotGeneric' => false,
            'customList' => '',
        ]);
    }

    /**
     * @Route("/create", name="Create")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request)
    {
        $newFaction = new Faction();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(FactionCreateType::class, $newFaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newFaction->setAuthorFaction($this->getUser());

            $em->persist($newFaction);
            $em->flush();

            $this->addFlash('notice', "La carte a bien été ajouté");

            return $this->redirectToRoute(self::$route . 'List');
        }

        return $this->render('generic/form.html.twig', [
            'title' => 'Créer une faction',
            'path' => self::$route,
            'form' => $form->createView(),
            'action' => self::$route . 'Create',
            'actionHasParameter' => false,
            'id' => ''
        ]);
    }

    /**
     * @Route("/edit/{id}", name="Edit")
     * @ParamConverter("faction", options={"mapping"={"id"="id"}})
     */
    public function edit(Request $request, Faction $faction)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(FactionCreateType::class, $faction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($faction);
            $em->flush();

            $this->addFlash('notice', "Le jeu a bien été mis à jour");

            return $this->redirectToRoute(self::$route . 'List');
        }

        return $this->render('generic/form.html.twig', [
            'title' => 'Editer une faction',
            'path' => self::$route,
            'form' => $form->createView(),
            'action' => self::$route . 'Edit',
            'actionHasParameter' => true,
            'id' => $faction->getId(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="Delete")
     * @ParamConverter("faction", options={"mapping"={"id"="id"}})
     */
    public function delete(Faction $faction)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($faction);
        $em->flush();

        $this->addFlash('notice', "La faction a bien été supprimé");

        return $this->redirectToRoute(self::$route . 'List');
    }
}
