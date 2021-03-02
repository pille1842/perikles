<?php

namespace App\Form;

use App\Entity\Poll;
use App\Entity\Voter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('options', CollectionType::class, [
                'entry_type' => OptionType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
            ->add('voters', EntityType::class, [
                'class' => Voter::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Poll::class,
        ]);
    }
}
