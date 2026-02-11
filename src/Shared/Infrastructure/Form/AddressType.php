<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Form;

use GardenManager\Shared\Infrastructure\Form\DataTransformer\AddressTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'required' => false,
                'label' => 'Street',
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'City',
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
                'label' => 'Postal code',
            ])
            ->add('country', CountryType::class, [
                'required' => false,
                'label' => 'Country',
                'placeholder' => 'Select a country',
            ])
            ->addModelTransformer(new AddressTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
        ]);
    }
}
