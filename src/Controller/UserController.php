<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Friendship;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\UserType;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/user')]

class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function showUsers(UserRepository $userRepository): Response
    
    {
        return $this->render('user/users_index.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    #[Route('/user/{id}', name: 'app_user')]
    public function showProfile(User $user, FriendshipRepository $friendshipRepository, EntityManagerInterface $entityManager, Request $request): Response
    {   
        $friendshipRequests = $friendshipRepository->findBy(['recipient' => $user->getId(), 'accepted' => false]);
        $friends = $user->getFriends();
        $comments = $user->getComments();
        $writer = $this->getUser();

        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setWriter($writer);
            $comment->setUser($this->getUser());
            $comment->setCreatedAt(new \DateTimeImmutable());
            $user->addComment($comment);
    
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_user', ['id' => $user->getId()]);
        }

        return $this->render('user/profile.html.twig', [

            'user' => $user,
            'friendshipRequests' => $friendshipRequests,
            'friends' => $friends,
            'comments' => $comments,
            'commentForm' => $commentForm->createView(),
        ]);
    }

    #[Route('/user/{id}/edit', name:'app_user_edit', methods:['GET', 'POST'])]
    public function editProfile(User $user, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $file = $form->get('img')->getData();
        $selectedGames = $form->get('games')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            
            if($selectedGames){
                foreach ($selectedGames as $game) {
                    $user->addGame($game);
                    $entityManager->persist($game);
                    $game->addEndorsement();
                }
                
            }
            
            if($file){
            $file = $form->get('img')->getData();
                $originalNameFile = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName = $originalNameFile.uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('profile_directory'), $newFileName);

                $oldFileName = $user->getImg();
                if ($oldFileName && file_exists($this->getParameter('profile_directory') . '/' . $oldFileName)) {
                    unlink($this->getParameter('profile_directory') . '/' . $oldFileName);
                }

                $user->setImg($newFileName);
            }

            $userRepository->save($user, true);
            $entityManager->flush();

            return $this->redirectToRoute('app_user', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('user/edit.html.twig', [
            
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('moderation_app');
    }
}
