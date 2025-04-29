<?php

namespace App\Form;

use App\Entity\Categorie;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', ChoiceType::class, [
                'label' => 'Désignation',
                'choices' => [
                    'Ingénieur de son' => 'Ingenieur son',
                    'Studio' => 'Studio',
                    'Videaste' => 'Vidéaste',
                    'Figurants / Danseurs' => 'Figurants / danseurs',
                    'Photographe' => 'Photographe',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est obligatoir',
                    ])
                ]
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => "Image de couverture (Png, jpg et jpeg)",
                'required' =>  false,
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'image_uri'     =>  false,
                'download_uri'     =>  false,
                'imagine_pattern'   =>  'large_avatar',
            ])
            ->add('iconeFile', VichImageType::class, [
                'label' => "Image d'avant plan (Png, jpg et jpeg)",
                'required' =>  false,
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'image_uri'     =>  false,
                'download_uri'     =>  false,
                'imagine_pattern'   =>  'large_avatar',
            ])
            ->add('description', CKEditorType::class, [
                'required' => false,
            ])
            ->add('position', NumberType::class, [
                'label' => "Position",
                'help' => "Ordre d'affichage",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est obligatoir',
                    ])
                ]
            ])
            ->add('hexColor', ColorType::class, [
                'label' => 'Couleur hexadecimal',
                'attr' => ['class'=>'p-1'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est obligatoir',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
