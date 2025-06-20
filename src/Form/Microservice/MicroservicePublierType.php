<?php

namespace App\Form\Microservice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MicroservicePublierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question1', CheckboxType::class, [
                'label' => "J’ai vérifié attentivement le contenu de mon service avant sa mise en ligne.",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis!',
                    ]),
                ],
            ])
            ->add('question2', CheckboxType::class, [
                'label' => "Mon service est conforme aux Conditions Générales d’Utilisation de Talengo.io.",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis!',
                    ]),
                ],
            ])
            ->add('question3', CheckboxType::class, [
                'label' => "Je m’engage à adopter un comportement professionnel et respectueux envers mes clients.",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis!',
                    ]),
                ],
            ])
            ->add('question4', CheckboxType::class, [
                'label' => "Je m’abstiendrai de proposer ou de réaliser toute transaction en dehors de la plateforme
Talengo.io.",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis!',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
