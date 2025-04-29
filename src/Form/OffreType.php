<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Offre;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Désignation',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide'
                    ])
                ]
            ])
            ->add('tarif', IntegerType::class, [
                
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide'
                    ])
                ]
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => "Avant plan (Png, jpg et jpeg)",
                'required' =>  false,
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'image_uri'     =>  false,
                'download_uri'     =>  false,
                'imagine_pattern'   =>  'midle_size',
            ])
            ->add('couvertureFile', VichImageType::class, [
                'label' => "Couverture (Png, jpg et jpeg)",
                'required' =>  false,
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'image_uri'     =>  false,
                'download_uri'     =>  false,
                'imagine_pattern'   =>  'midle_size',
            ])
            ->add('categorie', EntityType::class, [
                'label' => 'Services',
                'class' => Categorie::class,
                'help' => "Joindre des catégories de services à ce pack",
                'required' => false,
                'multiple'     =>  true,
                'autocomplete'     =>  true,
                'attr' => ['class' => "p-0 border-0"],
                'placeholder'     =>  '--Sélectionnez des catégories de services --',
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('online', CheckboxType::class, [
                'label' => 'Mettre en ligne',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}
