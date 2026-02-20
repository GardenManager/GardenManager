<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Form;

use GardenManager\Tenant\Application\Dto\TenantFormDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TenantFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Tenant name',
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => $options['submit_label'],
                    'row_attr' => ['class' => 'flex justify-end mt-4'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TenantFormDto::class,
            'submit_label' => 'Save',
        ]);
    }
}
