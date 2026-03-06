<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Form;

use GardenManager\CustomAttribute\Application\Dto\DefinitionFormDto;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-extends AbstractType<DefinitionFormDto>
 *
 * @psalm-suppress TooManyTemplateParams
 */
final class DefinitionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add(
                'name',
                Type\TextType::class,
                [
                    'label' => 'Machine name',
                    'help' => 'Lowercase letters, numbers, and underscores only. Cannot be changed after creation.',
                    'disabled' => $isEdit,
                ]
            )
            ->add(
                'label',
                Type\TextType::class,
                [
                    'label' => 'Display label',
                ]
            )
            ->add(
                'entityType',
                Type\ChoiceType::class,
                [
                    'label' => 'Entity type',
                    'choices' => [
                        'Plant' => 'plant',
                    ],
                    'disabled' => $isEdit,
                ]
            )
            ->add(
                'type',
                Type\EnumType::class,
                [
                    'label' => 'Field type',
                    'class' => AttributeTypeEnum::class,
                    'choice_label' => static fn (AttributeTypeEnum $choice): string => ucfirst($choice->value),
                    'placeholder' => 'Select a type',
                    'disabled' => $isEdit,
                ]
            )
            ->add(
                'required',
                Type\CheckboxType::class,
                [
                    'label' => 'Required',
                    'required' => false,
                ]
            )
            ->add(
                'sortOrder',
                Type\IntegerType::class,
                [
                    'label' => 'Sort order',
                    'required' => false,
                ]
            )
            ->add(
                'optionsText',
                Type\TextareaType::class,
                [
                    'label' => 'Options (one per line)',
                    'help' => 'Only for SELECT type. Enter each option on a new line.',
                    'required' => false,
                ]
            )
            ->add(
                'submit',
                Type\SubmitType::class,
                [
                    'label' => $options['submit_label'],
                    'row_attr' => ['class' => 'flex justify-end mt-4'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DefinitionFormDto::class,
            'submit_label' => 'Save',
            'is_edit' => false,
        ]);
    }
}
