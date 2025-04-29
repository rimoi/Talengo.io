<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\SearchUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchUserType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options): void
   {
      $builder
         ->add('name', TextType::class, [
            'label' =>  false,
            'required'  =>  false,
            'attr'  =>  [
               'placeholder'   =>  'Nom, prénom', 'class' =>  'w-100 shadow-none'
            ]
         ])
         ->add('email', TextType::class, [
            'label' =>  false,
            'required'  =>  false,
            'attr'  =>  [
               'placeholder'   =>  'Email', 'class' =>  'w-100 shadow-none'
            ]
         ])
         ->add('genre', ChoiceType::class, [
            'label' => false,
            'required' => false,
            'expanded' => true,
            'placeholder' => 'Tout',
            'choices' => [
               'Homme' => 'Homme',
               'Femme' => 'Femme',
            ]
         ])
         ->add('compte', ChoiceType::class, [
            'label' => false,
            'required' => false,
            'expanded' => true,
            'placeholder' => 'Tout',
            'choices' => [
               'Administrateur' => 'Admin',
               'Client' => 'Client',
               'Vendeur' => 'Vendeur',
            ]
         ])
         ->add('isVerified', ChoiceType::class, [
            'label' => false,
            'required' => false,
            'expanded' => true,
            'placeholder' => 'Tout',
            'choices' => [
               'Actif' => 1,
               'Inactif' => 0,
            ]
         ])
         ->add('ville', TextType::class, [
            'label' =>  'Ville',
            'required'  =>  false,
            'attr'  =>  [
               'placeholder'   =>  'Ville', 'class' =>  'w-100 shadow-none'
            ]
         ])
         ->add('categories', EntityType::class, [
            'label' => false,
            'class' => Categorie::class,
            'choice_label'  => 'name',
            'placeholder' => 'Catégorie',
            //'autocomplete' => true,
            'expanded' => true,
            'required' => false,
            'multiple' => true,
         ]);
   }

   public function configureOptions(OptionsResolver $resolver): void
   {
      $resolver->setDefaults([
         'data_class' => SearchUser::class,
         'method' => 'GET',
         'csrf_protection' => false,
      ]);
   }

   public function getBlockPrefix()
   {
      return '';
   }
}
