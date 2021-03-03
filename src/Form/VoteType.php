<?php

namespace App\Form;

use App\Entity\Option;
use App\Entity\Poll;
use App\Entity\Vote;
use App\Validator\IsEqualToSha512;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $passcodeValidator = new IsEqualToSha512(['hash' => $options['passcode']]);
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
                'help' => 'The passcode is case sensitive and must be entered including the separating dashes (-).',
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
