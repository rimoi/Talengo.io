<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\SearchService;
use App\Repository\CategorieRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ville', TextType::class, [
                'label' =>  false,
                'required'  =>  false,
                'mapped'  =>  true,
                'attr'  =>  [
                    'placeholder'   =>  'Votre ville',
                    'class' => 'form-control border-0 form-control-lg border-0 bg-transparent',
                ]
            ])
            ->add('address', HiddenType::class, [
                'label' =>  false,
                'mapped' => false,
                'required'  =>  false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchService::class,
//            'method' => 'GET',
//            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
