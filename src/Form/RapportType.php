<?php

namespace App\Form;

use App\Entity\Rapport;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichFileType;

class RapportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', CKEditorType::class, [
                'label' => "Dites nous comment ça s'est passé, rédigez votre rapport de fin de prestation",
                'constraints' => [
                    new NotBlank()
                ]
            ])
            /*->add('rapportFile', VichFileType::class, [
                'label' => "Joindre une pièce justificative*",
                'help' => "Tout format (png, jpg, pdf...) Taille maximale 50MB",
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'download_uri'     =>  false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez joindre une pièce justificative',
                    ])
                ]
            ])*/;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rapport::class,
        ]);
    }
}
