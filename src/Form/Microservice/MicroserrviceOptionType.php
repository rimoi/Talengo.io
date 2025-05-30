<?php

namespace App\Form\Microservice;

use App\Entity\EmploisTemps;
use App\Entity\Microservice;
use App\Entity\Realisation;
use App\Form\RealisationType;
use App\Form\ServiceOptionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MicroserrviceOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('realisations', CollectionType::class, [
//                'entry_type' => RealisationType::class,
//                'by_reference' => false,
//                'required' => false,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'error_bubbling' => false
//            ])
            ->add('promo', CheckboxType::class, [
                'label' => 'En promootion',
                'help' => "Cocher pour mettre se service en promotion",
                'required' => false
            ])
            ->add('serviceOptions', CollectionType::class, [
                'entry_type' => ServiceOptionType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Microservice::class,
        ]);
    }
}
