<?php

namespace App\Form;

use App\Entity\CommandeMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichFileType;

class CommandeMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('messageFile', VichFileType::class, [
                'label' => 'File',
                'required' => false,
            ])
            ->add('contenu', TextareaType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Ecrivez votre message'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ecrivez votre message'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandeMessage::class,
        ]);
    }
}
