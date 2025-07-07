<?php

namespace App\Form;

use App\Entity\Realisation;
use App\Form\Microservice\EditMediaType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Réalisation'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('imageFile', VichImageType::class, [
                'label' =>  'Format (Png, jpg et jpeg)',
                'allow_delete' =>  false,
                'download_label' =>  false,
                'image_uri' =>  false,
                'download_uri' =>  false,
                'imagine_pattern' =>  'large_avatar',
                'attr' => ['class' => ''],
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est de {{ limit }} {{ suffix }}'
                    ])
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Realisation::class,
        ]);
    }
}
