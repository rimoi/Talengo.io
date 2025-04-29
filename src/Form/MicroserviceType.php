<?php

namespace App\Form;

use App\Entity\Microservice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MicroserviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('description')
            ->add('online')
            ->add('delai')
            ->add('prixMastering')
            ->add('prixMixage')
            ->add('prixBeatmaking')
            ->add('created')
            ->add('updated')
            ->add('medias')
            ->add('vendeur')
            ->add('categorie')
            ->add('prix')
            ->add('serviceOptions')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Microservice::class,
        ]);
    }
}
