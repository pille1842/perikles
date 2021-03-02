<?php

namespace App\Form;

use App\Entity\Option;
use App\Entity\Poll;
use App\Entity\Vote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;

class VoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $passcodeValidator = new EqualTo($options['passcode']);
        // Override the message since it would give the user the actual passcode
        $passcodeValidator->message = "The passcode is invalid.";

        $builder
            ->add('votefor', EntityType::class, [
                'class' => Option::class,
                'choice_label' => 'label',
                'choices' => $options['poll']->getOptions(),
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('passcode', TextType::class, [
                'mapped' => false,
                'required' => true,
                'constraints' => [$passcodeValidator],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vote::class,
            'poll' => null,
            'passcode' => null,
        ]);

        $resolver->setAllowedTypes('poll', Poll::class);
        $resolver->setAllowedTypes('passcode', 'string');
    }
}
