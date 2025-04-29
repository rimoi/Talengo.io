<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CategorieUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lieuPrestation', TextType::class, [
                'label' => 'Lieu de prestation',
                'help' => 'Ne concerne que les catégories ingénieure son et studio',
                'required' => false,
                'attr' => ['class' => '', 'placeholder' => 'Lieu de prestation'],
            ])
            ->add('homeStudio', CheckboxType::class, [
                'label'     =>  'Home studio',
                'required' => false,
                'help'     =>  "Cocher uniquement s'il s'agit d'un Home studio",
            ])
            ->add('couvertureFile', VichImageType::class, [
                'label' => '(Png, jpg et jpeg)',
                'required'  =>  false,
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'image_uri'     =>  false,
                'download_uri'     =>  false,
                'imagine_pattern'   =>  'rectangle_avatar',
                'attr'   =>  ['class' => 'form-control-file'],
            ])
            ->add('categorie', EntityType::class, [
                'label'     =>  'Catégorie de prestataire',
                'expanded'     =>  true,
                'attr' => ['class' => ""],
                'placeholder'     =>  '--Catégorie de prestataire--',
                'class' => Categorie::class,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ])
                ],
            ])
            ->add('rib', TextType::class, [
                'label' => 'RIB',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ]),
                ],
                'attr' => ['class' => '', 'placeholder' => 'RIB'],
            ])
            ->add('iban', TextType::class, [
                'label' => 'IBAN',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ]),
                ],
                'attr' => ['class' => '', 'placeholder' => 'IBAN'],
            ])
            ->add('siret', TextType::class, [
                'label' => 'Numéro Siret',
                'required' => false,
                'attr' => ['class' => '', 'placeholder' => 'Numéro Siret'],
            ])
            ->add('paypal', TextType::class, [
                'label' => 'Paypal',
                'help' => 'Facultatif',
                'required' => false,
                'attr' => ['class' => '', 'placeholder' => 'Adresse Paypal'],
            ])
            ->add('stripe', TextType::class, [
                'label' => 'Stripe',
                'help' => 'Facultatif',
                'required' => false,
                'attr' => ['class' => '', 'placeholder' => 'Adresse Stripe'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
