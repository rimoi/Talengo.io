<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CoordonneeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom*',
                'attr' => ['placeholder' => 'Nom'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom*',
                'attr' => ['placeholder' => 'Prénom'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre*',
                'choices'  => [
                    'Homme' =>  'Homme',
                    'Femme'    =>  'Femme',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudonyme',
                'attr' => ['placeholder' => 'Pseudonyme'],
                'required' => false,
                'help' => "Un pseudonyme est un nom d'emprunt adopté par une ou plusieurs personnes pour exercer une activité sous un autre nom que celui de son identité officielle.",
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('usePseudo', CheckboxType::class, [
                'label' => 'Utilisé le pseudonyme comme votre nom public',
                'help' => "Au lieu que votre nom et pénom soit afficher sur le site, c'est votre pseudonyme  qui sera concideré comme votre nom public",
                'required' => false
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => '
        <div class="upload-label">
            <div class="mb-1">
                <i class="fas fa-chart-line mr-1 text-primary"></i>
                <strong>Boostez votre crédibilité : </strong> les vendeurs avec une vraie photo de profil génèrent plus
de ventes que la moyenne !
            </div>
            <div class="small text-muted">
                <i class="fas fa-file-image mr-1"></i>
                Formats : PNG, JPG, JPEG
            </div>
        </div>
    ',
                'label_html' => true,
                'required'  =>  false,
                'allow_delete' =>  false,
                'download_label'     =>  false,
                'image_uri'     =>  false,
                'download_uri'     =>  false,
                'imagine_pattern'   =>  'identite_size',
                'attr'   =>  ['class' => ''],
                'invalid_message' => 'Le fichier est trop volumineux',
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est de {{ limit }} {{ suffix }}.'
                    ])
                ],
            ])
            ->add('categorie', EntityType::class, [
                'label'     =>  'Catégorie de prestataire',
                'expanded'     =>  true,
                'attr' => ['class' => ""],
                'placeholder'     =>  '--Catégorie de prestataire--',
                'class' => Categorie::class,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis',
                    ])
                ],
            ])
            ->add('apropos', CKEditorType::class, [
                'label' => 'Biographie',
                'help' => "Votre biographie est votre premier levier de vente. <br>
Elle transmet votre professionnalisme, valorise votre parcours et crée une première impression décisive.<br>
Une présentation soignée capte l’attention, inspire confiance et déclenche l’envie de travailler avec vous.<br>
Une biographie bien construite met en lumière votre expertise, affirme votre valeur ajoutée et vous
positionne comme un partenaire fiable. <br>Elle reflète votre engagement, votre sérieux et votre capacité à
répondre aux besoins des clients.",
                'help_html' => true,
                'required' => false,
                'attr' => ['rows' => 6],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'help' => 'Exemple (N° Ruelle/Avenue)',
                'attr' => ['placeholder' => 'Adresse', 'autocomplete' => "address-line1"],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'help' => 'Ville de résidence actuelle',
                'attr' => ['placeholder' => 'Ville', 'autocomplete' => "address-level2"],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'help' => 'Pays de résidence actuelle',
                'attr' => ['placeholder' => 'Pays de résidence', 'autocomplete' => "country"],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['placeholder' => 'Code postal'],
                //'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                ],
            ])
            ->add('telephone', TextType::class, [
                'help' => 'Facultatif',
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => 'Téléphone'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'help' => 'Exemple: email@domaine.com',
                'attr' => ['placeholder' => 'Exemple@domail.com'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide',
                    ]),
                    new Email([
                        'message' => 'Veuillez saisir une adresse email valide',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
