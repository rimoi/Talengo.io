<?php

namespace App\Form;

use App\Entity\Prix;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PrixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', ChoiceType::class, [
                'label' => 'Plan',
                'choices'  => [
                    'Basique'    =>  'Basique',
                    'Standard'    =>  'Standard',
                    'Premium'    =>  'Premium',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peux pas être vide',
                    ]),
                ],
            ])
            ->add('tarif', IntegerType::class, [
                'label' => 'Tarif',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peux pas être vide',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 6, 'placeholder' => 'Soyez bref'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prix::class,
        ]);
    }
}
