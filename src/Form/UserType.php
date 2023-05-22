<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Game;
use App\Entity\Gameplay;
use App\Entity\Profile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Username'
            ])
            ->add('contact', TextType::class, [
                'label' => 'Discord',
            ])
            ->add('img', FileType::class, [
                'label' => 'Avatar: ',
                'mapped' => false,
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Introduce yourself: ',
            ])
            ->add('mentor', ChoiceType::class, [
                'label' => 'Do you wanna be part of the Adopt a Noob program ?',
                'placeholder' => 'My behaviour toward beginners',
                'choices' => [
                    'I wanna help beginners' => 1,
                    'That\'s not for me' => 0,
                ],
                'mapped' => false,
                'required' => false,
            ])
            ->add('games', EntityType::class, [
                'label' => 'My Games',
                'class' => Game::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);            
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
