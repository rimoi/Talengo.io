<?php

namespace App\Form\Microservice;

use App\Entity\Microservice;
use App\Entity\Tag;
use App\Form\PrixType;
use App\Form\TagType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class DescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix', IntegerType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide'
                    ]),
                    new GreaterThan([
                        'value' => 0,
                    ])
                ],
                'attr' => ['class' => 'border-0']
            ])
            ->add('nombreJour', IntegerType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide'
                    ]),
                    new GreaterThan([
                        'value' => 0,
                    ])
                ],
                'attr' => ['class' => 'border-0']
            ])
            ->add('tags', CollectionType::class, [
                'entry_type' => TagType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('description', CKEditorType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['rows' => 8, 'class' => 'font-weight-bold text-muted', 'placeholder' => 'Description'], 'constraints' => [
                    new NotBlank([
                        'message' => 'Une description est requise',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Microservice::class,
        ]);
    }
}
