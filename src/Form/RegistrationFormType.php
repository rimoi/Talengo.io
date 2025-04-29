<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'nom'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'prénom'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ]),
                ],
            ])
            ->add('compte', ChoiceType::class, [
                'label' => 'Que souhaitez vous?',
                'choices'  => [
                    'Acheter des prestations' =>  'Client',
                    'Vendre des prestations'    =>  'Vendeur',
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Que souhaiterez-vous?',
                    ]),
                ],
            ])
            /*->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'help' => 'Exemple (N° Ruelle/Avenue)',
                'attr' => ['placeholder' => 'Ville', 'autocomplete' => "address-line1"],
                'required' => false,
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'help' => 'Ville de residence actuelle',
                'attr' => ['placeholder' => 'Ville de residence', 'autocomplete' => "address-level2"],
                'required' => false,
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'help' => 'Ville de residence actuelle',
                'attr' => ['placeholder' => 'Ville de residence', 'autocomplete' => "country"],
                'required' => false,
            ])
            ->add('etat', TextType::class, [
                'label' => 'Etat',
                'help' => 'Etat de residence actuelle',
                'attr' => ['placeholder' => 'Ville de residence', 'autocomplete' => "address-level1"],
                'required' => false,
            ])*/
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Exemple@domail.com'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ]),
                    new Email([
                        'message' => 'Veuillez saisir une adresse email valide',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => false,
                'attr' => ['style' => 'display: inline!important;width:15px;height:15px;border: 1px solid #ededed;bottom:-18px;'],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Mot de passe'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit être au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            /*->add('longitude', HiddenType::class, [
                'required' => false,
            ])
            ->add('latitude', HiddenType::class, [
                'required' => false,
            ])*/;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
