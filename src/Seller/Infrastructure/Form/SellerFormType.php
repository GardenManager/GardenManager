<?php

namespace GardenManager\Seller\Infrastructure\Form;

use GardenManager\Seller\Application\Dto\CreateSellerDto;
use GardenManager\Seller\Application\Dto\UpdateSellerDto;
use GardenManager\Shared\Infrastructure\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SellerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Display name'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('phone', TelType::class, ['required' => false, 'label' => 'Phone'])
            ->add('description', TextareaType::class, ['required' => false, 'label' => 'Description'])
            ->add('address', AddressType::class, ['required' => false, 'label' => 'Address']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('data_class');
        $resolver->setAllowedValues('data_class', [CreateSellerDto::class, UpdateSellerDto::class]);
    }
}
