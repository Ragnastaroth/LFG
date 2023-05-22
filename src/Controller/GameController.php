<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/game')]
class GameController extends AbstractController
{
    #[Route('/', name: 'app_game_index', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findAll(),
        ]);
    }

    #[Route('/admin/new', name: 'app_game_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GameRepository $gameRepository, EntityManagerInterface $entityManager): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
        $file = $form->get('img')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($file){
                $file = $form->get('img')->getData();
                $originalNameFile = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName = $originalNameFile.uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('game_directory'), $newFileName);

                $oldFileName = $game->getImg();
                if ($oldFileName && file_exists($this->getParameter('game_directory') . '/' . $oldFileName)) {
                unlink($this->getParameter('game_directory') . '/' . $oldFileName);
                }
                $game->setImg($newFileName); 
            }

            $gameRepository->save($game, true);
            $entityManager->flush();

            $this->addFlash('success', 'Game successfully added !');

            return $this->redirectToRoute('moderation_app');
        }

        return $this->renderForm('game/new.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_game_show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        return $this->render('game/show.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'app_game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Game $game, GameRepository $gameRepository): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $gameRepository->save($game, true);

            return $this->redirectToRoute('moderation_app');
        }

        return $this->renderForm('game/edit.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_game_delete', methods: ['POST'])]
    public function delete(Request $request, Game $game, GameRepository $gameRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $gameRepository->remove($game, true);
        }

        return $this->redirectToRoute('moderation_app',);
    }
}
