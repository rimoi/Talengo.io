<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class InformationUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('lieuPrestation', TextType::class, [
//                'label' => 'Lieu de la prestation',
//                'help' => 'Exemple: studio privé',
//                'required' => false,
//                'attr' => ['class' => '', 'placeholder' => 'Lieu de la prestation'],
//            ])
//            ->add('homeStudio', CheckboxType::class, [
//                'label'     =>  'Home studio',
//                'required' => false,
//                'help'     =>  "Cocher uniquement s'il s'agit d'un Home studio",
//            ])
            ->add('couvertureFile', VichImageType::class, [
                'label' => '(Png, jpg et jpeg)',
                'required'  =>  false,
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'image_uri'     =>  false,
                'download_uri'     =>  false,
                'imagine_pattern'   =>  'rectangle_avatar',
                'attr'   =>  ['class' => 'form-control-file'],
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ taille }} {{ suffixe }}). La taille maximale autorisée est de {{ limite }}. {{ suffixe }}'
                    ])
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices'  => [
                    'Particulier' =>  'Particulier',
                    'Auto-entreprise ou entreprise individuelle'    =>  'Auto-entreprise ou entreprise individuelle',
                    'Société (France uniquement)'    =>  'Société (France uniquement)',
                    'Société (Autres pays)'    =>  'Société (Autres pays)',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            /*->add('recevedPaiement', CheckboxType::class, [
                'label' => "Recevoir les paiements",
                'help' => "Cochez si vous souhaitez recevoir automatiquement des paiements via votre compte bancaire après la vente de vos services",
                'required' => false,
                'attr' => ['class' => '', 'placeholder' => 'Adresse Stripe'],
            ])*/;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
