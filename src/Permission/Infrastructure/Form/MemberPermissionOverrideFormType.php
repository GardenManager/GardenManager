<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Form;

use GardenManager\Permission\Application\Dto\MemberPermissionOverrideFormDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-extends AbstractType<MemberPermissionOverrideFormDto>
 *
 * @psalm-suppress TooManyTemplateParams
 */
final class MemberPermissionOverrideFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'permission',
                ChoiceType::class,
                [
                    'label' => 'Permission',
                    'choices' => $options['permission_choices'],
                    'placeholder' => 'Select a permission',
                ],
            )
            ->add(
                'granted',
                ChoiceType::class,
                [
                    'label' => 'Action',
                    'choices' => [
                        'Grant' => true,
                        'Deny' => false,
                    ],
                ],
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Add Override',
                    'row_attr' => ['class' => 'flex justify-end mt-4'],
                ],
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberPermissionOverrideFormDto::class,
            'permission_choices' => [],
        ]);

        $resolver->setAllowedTypes('permission_choices', 'array');
    }
}
