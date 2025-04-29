<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Commande1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('offre')
            ->add('montant')
            ->add('validate')
            ->add('deliver')
            ->add('cancel')
            ->add('deliverAt')
            ->add('cancelAt')
            ->add('validateAt')
            ->add('statut')
            ->add('confirmationClient')
            ->add('lu')
            ->add('created')
            ->add('updated')
            ->add('microservice')
            ->add('client')
            ->add('vendeur')
            ->add('destinataire')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
