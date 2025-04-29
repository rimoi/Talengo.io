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
                'label' => "Avoir relu très attentivement mon service",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis!',
                    ]),
                ],
            ])
            ->add('question2', CheckboxType::class, [
                'label' => "Que celui-ci est conforme et respecte les CGU de Links Infinity",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis!',
                    ]),
                ],
            ])
            ->add('question3', CheckboxType::class, [
                'label' => "Toujours rester professionnel et courtois avec mes clients",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis!',
                    ]),
                ],
            ])
            ->add('question4', CheckboxType::class, [
                'label' => "Ne pas proposer ou effectuer des transactions en dehors de Links Infinity",
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
