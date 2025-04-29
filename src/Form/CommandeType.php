<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('montant', HiddenType::class)
        ->add('reservationDate', DateType::class, [
            'label' => 'Date de la reservation',
            'attr' => [
                'class' => "js-datepicker",
            ],
            'widget' => 'single_text','constraints' => [
                new NotBlank()
            ],
        ])
        /*->add('reservationStartAt', TimeType::class, [
            'widget' => 'single_text',
            'attr' => ['class' => ''],
            'constraints' => [
                new NotBlank([
                    'message' => 'Champ obligatoire!',
                ]),
            ]
        ])
        ->add('reservationEndAt', TimeType::class, [
            'widget' => 'single_text',
            'constraints' => [
                new NotBlank([
                    'message' => 'Champ obligatoire!',
                ]),
            ]
        ])*/
        ->add('startHoure', ChoiceType::class, [
            'label' => 'Heure de début',
            'placeholder' => '--Selectionnez--',
            'choices' => [
                '00:00' => 0,
                '01:00' => 1,
                '02:00' => 2,
                '03:00' => 3,
                '04:00' => 4,
                '05:00' => 5,
                '06:00' => 6,
                '07:00' => 7,
                '07:00' => 7,
                '09:00' => 9,
                '10:00' => 10,
                '11:00' => 11,
                '12:00' => 12,
                '13:00' => 13,
                '14:00' => 14,
                '15:00' => 15,
                '16:00' => 16,
                '17:00' => 17,
                '18:00' => 18,
                '19:00' => 19,
                '20:00' => 20,
                '21:00' => 21,
                '22:00' => 22,
                '23:00' => 23,
            ],
            'constraints' => [
                new NotBlank(),
            ]
        ])
        ->add('endHoure', ChoiceType::class, [
            'label' => 'Heure de fin',
            'placeholder' => '--Selectionnez--',
            'choices' => [
                '00:00' => 0,
                '01:00' => 1,
                '02:00' => 2,
                '03:00' => 3,
                '04:00' => 4,
                '05:00' => 5,
                '06:00' => 6,
                '07:00' => 7,
                '07:00' => 7,
                '09:00' => 9,
                '10:00' => 10,
                '11:00' => 11,
                '12:00' => 12,
                '13:00' => 13,
                '14:00' => 14,
                '15:00' => 15,
                '16:00' => 16,
                '17:00' => 17,
                '18:00' => 18,
                '19:00' => 19,
                '20:00' => 20,
                '21:00' => 21,
                '22:00' => 22,
                '23:00' => 23,
            ],
            'constraints' => [
                new NotBlank(),
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
