<?php

namespace App\Form;

use App\Domain\Lists;
use App\Entity\BloodRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BloodRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hospitals = Lists::allHospitals();

        $builder
            ->add('bloodType', ChoiceType::class, [
                'choices' => array_combine(Lists::BLOOD_TYPES, Lists::BLOOD_TYPES),
                'placeholder' => 'Select blood type',
            ])
            ->add('city', ChoiceType::class, [
                'choices' => array_combine(Lists::TUNISIA_CITIES, Lists::TUNISIA_CITIES),
                'placeholder' => 'Select city',
            ])
            ->add('hospitalName', ChoiceType::class, [
                'choices' => array_combine($hospitals, $hospitals),
                'placeholder' => 'Select a city first',
            ])
            ->add('urgency', ChoiceType::class, [
                'choices' => array_combine(Lists::URGENCIES, Lists::URGENCIES),
                'placeholder' => 'Select urgency',
            ])
            ->add('unitsNeeded', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                    'step' => 1,
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('contactPhone', TextType::class, [
                'attr' => [
                    'inputmode' => 'numeric',
                    'pattern' => '\d{8}',
                    'maxlength' => 8,
                    'minlength' => 8,
                    'autocomplete' => 'tel-national',
                    'placeholder' => '12345678',
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var BloodRequest $data */
            $data = $event->getData();
            $form = $event->getForm();

            $city = $data->getCity();
            $hospital = $data->getHospitalName();

            if ($city && $hospital && !Lists::hospitalBelongsToCity($city, $hospital)) {
                $form->get('hospitalName')->addError(
                    new FormError('Please select a hospital from the chosen city.')
                );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BloodRequest::class,
        ]);
    }
}
