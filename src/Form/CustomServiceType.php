<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\EmploisTemps;
use App\Entity\Microservice;
use App\Entity\ServiceOption;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('microservice', EntityType::class, [
                'class' => Microservice::class,
            ])
            ->add('serviceOptions', EntityType::class, [
                'label' => "Options supplémentaire",
                'class' => ServiceOption::class,
                'multiple' => true,
                'expanded' => false,

                /*'label' => false,
            'entry_options' => [
                'label' => false,
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false**/
            ])
            ->add('emploisTemps', EntityType::class, [
                'label' => "Prendre rendez-vous",
                'class' => EmploisTemps::class,
                'multiple' => true,
                'expanded' => false,
                'disabled' => false,
                'mapped' => false,
            ]);

            /*$builder->get('microservice')->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    
                    $form = $event->getForm(); //dd($form);
    
                    $this->addMicroserviceFields($form->getParent(), $form->getData());
               }
            );
    
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
    
                    $data = $event->getData();
                    /* @var $option serviceOption *
                    $option = $data->getServiceOptions();
    
                    $form = $event->getForm();
    
                    if ($option){
    
                        $ville = $option->getMicroservices();
                        $option = $ville->getServiceOptions();
                        
    
                        $this->addMicroserviceFields($form, $ville);
                        $this->addMicroserviceFields($form, $option);
    
                        $form->get('serviceOptions')->setData($option);
                        $form->get('micoservice')->setData($ville);
                        //$form->get('option')->setData($option);
                    }else{
                        $this->addMicroserviceFields($form, null);
                        $this->addMicroserviceFields($form, null);
                    }
                }
            );*/

        /*$formModifier = function (FormInterface $form, Microservice $microservice = null) {
            $options = null === $microservice ? array() : $microservice->getServiceOptions();
            dd($options);

            $form->add('serviceOptions', EntityType::class, array(
                'class'       => ServiceOption::class,
                'empty_data' => '',
                'choices'     => $options,
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getMicroservice());
            }
        );

        /*$builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();

                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();
                //dd($data->getMicroservice());

                $microservice = $data->getMicroservice();
                $options = $microservice->getServiceOptions();
                dd($options);

                $form->getConfig()->getFormFactory()->createNamedBuilder(
                    'serviceOption',
                    EntityType::class,
                    null,
                    [
                        'class' => ServiceOption::class,
                        'placeholder' => '',
                        'auto_initialize'   =>  false,
                        'choices' => $options,
                    ]
                );
            }
        );*/
    }

    /**
     * Rajoute un champ de type VilleType au formulaire
     *
     * @param FormInterface $form
     * @param ServiceOption $option
     * @return void
     */
    private function addMicroserviceFields(FormInterface $form, ?ServiceOption $option){

        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'serviceOptions', 
            EntityType::class, 
            null,
            [
                'class' =>  ServiceOption::class,
                'label' => 'Option*',
                //'help' => 'Ville',
                'attr' => [
                    'class' => ''
                ],
                'mapped'    =>  false,
                'auto_initialize'   =>  false,
                'placeholder'   =>  $option ? 'Ville' : 'Sélectionnez un Département',
                'choices'   =>  $option ? $option->getVilles() : [],
                //'required'  =>  false,
            ]
        );
                
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event){
                $form = $event->getForm();
                $this->addArrondissementFileds($form->getParent(), $form->getData());
            }
        );

        $form->add($builder->getForm());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
