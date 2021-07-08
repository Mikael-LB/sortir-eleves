<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class,[
                'label' => 'Nom de votre Sortie :'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de début de votre sortie :'
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date de cloture des inscriptions :'
            ])
            ->add('nbInscriptionsMax', NumberType::class, [
                'label' => 'Nombre MAX de participant (vous exclu) :'
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée en minutes de votre Sortie (1H=60 , 1 semaine=10 080) :'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos :'
            ])
//            ->add('campus')
//            ->add('lieu')
            ->add('Ville', EntityType::class,[
                'label' => 'Ville :',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false
            ])
            ->add('Lieu', EntityType::class, [
                'label' => 'Lieu :',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'mapped' => false,
                'inherit_data' => Ville::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
