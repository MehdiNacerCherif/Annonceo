<?php

namespace App\Form;

use App\Entity\Note;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('note')
            ->add('avis')
            // ->add('creation')
            // ->add('auteur')
            // ->add('utilisateur')
            ->add('utilisateur', EntityType::class, [
                "class" => Utilisateur::class,
                "choice_label" => "pseudo"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
        ]);
    }
}
