<?php

namespace App\Form;

use App\BO\Filtrer;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltrerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class,[
                'class'=>Campus::class,
                'choice_label'=>'nom',
            ])
            ->add('nom', TextType::class,[
                'label' => 'Le nom de la sortie contient',
            ])
            ->add('dateHeureDebut', DateType::class,[
                'html5'=>true,
                'widget'=>'single_text'
            ])
            ->add('dateHeureFin', DateType::class,[
                'html5'=>true,
                'widget'=>'single_text'
            ])
            ->add('isOrganisateur', CheckboxType::class, [
                'label'=>'Sorties dont je suis l\'organisateur/trice',
                'required'=>false,])
            ->add('isInscrit', CheckboxType::class, [
                'label'=>'Sorties auxquelles je suis inscrit/e',
                'required'=>false,])
            ->add('notInscrit', CheckboxType::class, [
                'label'=>'Sorties auxquelles je ne suis pas inscrit/e',
                'required'=>false,])
            ->add('oldSorties', CheckboxType::class, [
                'label'=>'Sorties passées',
                'required'=>false,])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filtrer::class,
        ]);
    }
}
