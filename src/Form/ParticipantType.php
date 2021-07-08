<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('email')
            ->add('PlainePassword', RepeatedType::class,[
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Entrer votre mot de passe',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit faire {{ limit }} caractères au minimum',
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Mot de passe :',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Confirmation :',
                ],
                'invalid_message' => 'Les deux champs doivent etre identique',
                'mapped' => false
            ])
            ->add('estAdministrateur')
            ->add('campus', EntityType::class,[
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('urlImage', FileType::class,[
                'mapped' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '7000k',
                        'mimeTypesMessage' => "Format d'Image non supporté !"
                    ])
                ],
                'label' => 'Ma photo :',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
