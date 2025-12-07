<?php

namespace App\Form;

use App\Entity\DonationOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class DonationOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Length(
                        min: 5,
                        max: 1000,
                        minMessage: 'Your message should be at least {{ limit }} characters.',
                        maxMessage: 'Your message cannot be longer than {{ limit }} characters.',
                    ),
                ],
                'attr' => [
                    'rows' => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DonationOffer::class,
        ]);
    }
}
