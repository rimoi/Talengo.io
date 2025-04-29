<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserMethodePaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rib', TextType::class, [
                'label' => 'RIB*',
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
            ])
            /*->add('recevedPaiement', CheckboxType::class, [
                'label' => "Recevoir les paiements",
                'help' => "Cochez si vous souhaitez recevoir automatiquement des paiements via votre compte bancaire après la vente de vos services",
                'required' => false,
                'attr' => ['class' => '', 'placeholder' => 'Adresse Stripe'],
            ])*/;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
