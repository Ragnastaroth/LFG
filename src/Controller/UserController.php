<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_user')]
    public function showProfile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            
            'user' => $user,
        ]);
    }

    #[Route('user/{id}/edit', name:'app_user_edit', methods:['GET', 'POST'])]
    public function editProfile(User $user, UserRepository $userRepository, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('img')->getData();
                $originalNameFile = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName = $originalNameFile.uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $newFileName);
                $user->setImg($newFileName);
            
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('user/edit.html.twig', [
            
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

}
