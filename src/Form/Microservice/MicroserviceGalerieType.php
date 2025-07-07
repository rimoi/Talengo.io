<?php

namespace App\Form\Microservice;

use App\Entity\Microservice;
use App\Form\MediaType;
use App\Form\Microservice\EditMediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MicroserviceGalerieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('medias', CollectionType::class, [
                'entry_type' => EditMediaType::class,
                'by_reference' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
                'entry_options' => [
                    'error_bubbling' => false,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Microservice::class,
        ]);
    }
}
