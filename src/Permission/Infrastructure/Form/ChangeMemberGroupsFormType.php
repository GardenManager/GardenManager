<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-extends AbstractType<null>
 *
 * @psalm-suppress TooManyTemplateParams
 */
final class ChangeMemberGroupsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'groupSlugs',
                ChoiceType::class,
                [
                    'label' => 'Groups',
                    'choices' => $options['group_choices'],
                    'multiple' => true,
                    'expanded' => true,
                ],
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Update Groups',
                    'row_attr' => ['class' => 'flex justify-end mt-4'],
                ],
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'group_choices' => [],
        ]);

        $resolver->setAllowedTypes('group_choices', 'array');
    }
}
