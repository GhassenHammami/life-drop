<?php

namespace App\Form;

use App\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Domain\Lists;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full name',
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone number',
                'required' => true,
            ])
            ->add('city', ChoiceType::class, [
                'label' => 'City',
                'placeholder' => 'Select your city',
                'choices' => array_combine(Lists::TUNISIA_CITIES, Lists::TUNISIA_CITIES),
            ])
            ->add('bloodType', ChoiceType::class, [
                'label' => 'Blood type (if you want to donate)',
                'required' => false,
                'placeholder' => 'Select your blood type',
                'choices' => array_combine(Lists::BLOOD_TYPES, Lists::BLOOD_TYPES),
            ])
            ->add('lastDonationDate', DateType::class, [
                'label' => 'Last donation date (optional)',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('available', CheckboxType::class, [
                'label' => 'I’m available to donate',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
