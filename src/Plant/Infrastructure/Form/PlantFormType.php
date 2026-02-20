<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Form;

use GardenManager\Plant\Application\Dto\PlantFormDto;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-extends AbstractType<PlantFormDto>
 *
 * @psalm-suppress TooManyTemplateParams
 */
final class PlantFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'localName',
                Type\TextType::class,
                [
                    'label' => 'Local name',
                ],
            )
            ->add(
                'genus',
                Type\TextType::class,
                [
                    'label' => 'Genus',
                    'required' => false,
                ],
            )
            ->add(
                'epithet',
                Type\TextType::class,
                [
                    'label' => 'Epithet',
                    'required' => false,
                ],
            )
            ->add(
                'isHybrid',
                Type\CheckboxType::class,
                [
                    'label' => 'Hybrid',
                    'required' => false,
                ],
            )
            ->add(
                'cultivar',
                Type\TextType::class,
                [
                    'label' => 'Cultivar',
                    'required' => false,
                ],
            )
            ->add(
                'lifecycle',
                Type\EnumType::class,
                [
                    'label' => 'Lifecycle',
                    'class' => LifecycleEnum::class,
                    'choice_label' => static fn (LifecycleEnum $choice): string => ucfirst($choice->value),
                    'placeholder' => 'Select a status',
                ],
            )
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'row_attr' => ['class' => 'flex justify-end mt-4'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlantFormDto::class,
            'submit_label' => 'Save',
        ]);
    }
}
