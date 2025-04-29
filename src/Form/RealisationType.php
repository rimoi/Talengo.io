<?php

namespace App\Form;

use App\Entity\Realisation;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class RealisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Désignation',
                'attr' => ['placeholder' => 'Désingation'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de réalisation',
                'expanded' => true,
                'choices' => [
                    'Vidéo' => 'video',
                    'Image' => 'image',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ])
                ]
            ])
            ->add('plateforme', ChoiceType::class, [
                'label' => 'Sélectionnez une source',
                'expanded' => true,
                'choices' => [
                    'YouTube' => 'youtube',
                    'Instagram' => 'instagram',
                    'Autre' => 'Autre',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ])
                ]
            ])
            ->add('link', UrlType::class, [
                'label' => 'Lien vers la réalisation',
                'help' => 'Copiez vos liens YouTube, Vimeo,... pour referencer vos réalisations',
                'constraints' => [
                    new NotBlank(),
                    new Url()
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Realisation::class,
        ]);
    }
}
