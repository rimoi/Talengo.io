<?php

namespace App\Form;

use App\Entity\FAQ;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FAQType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Question',
                'attr' => ['placeholder' => "Posez votre question."],
            ])
            ->add('description', TextType::class, [
                'label' => 'Réponse',
                'attr' => ['placeholder' => "Apportez votre réponse."]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FAQ::class,
        ]);
    }
}
