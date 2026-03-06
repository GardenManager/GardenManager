<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Form;

use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 *
 * @psalm-suppress TooManyTemplateParams
 */
final class CustomAttributeValuesType extends AbstractType
{
    public function __construct(
        private readonly CustomAttributeDefinitionRepositoryInterface $definitionRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entityType = $options['entity_type'];
        $definitions = $this->definitionRepository->findByEntityType($entityType);

        foreach ($definitions as $definition) {
            $definitionId = $definition->getId()->toString();

            $fieldOptions = [
                'label' => $definition->getLabel(),
                'required' => $definition->isRequired(),
                'mapped' => false,
            ];

            match ($definition->getType()) {
                AttributeTypeEnum::STRING => $builder->add(
                    $definitionId,
                    Type\TextType::class,
                    $fieldOptions,
                ),
                AttributeTypeEnum::INTEGER => $builder->add(
                    $definitionId,
                    Type\IntegerType::class,
                    $fieldOptions,
                ),
                AttributeTypeEnum::DECIMAL => $builder->add(
                    $definitionId,
                    Type\NumberType::class,
                    $fieldOptions + [
                        'scale' => 4,
                    ],
                ),
                AttributeTypeEnum::DATE => $builder->add(
                    $definitionId,
                    Type\DateType::class,
                    $fieldOptions + [
                        'widget' => 'single_text',
                    ],
                ),
                AttributeTypeEnum::BOOLEAN => $builder->add(
                    $definitionId,
                    Type\CheckboxType::class,
                    $fieldOptions + [
                        'required' => false,
                    ],
                ),
                AttributeTypeEnum::SELECT => $builder->add(
                    $definitionId,
                    Type\ChoiceType::class,
                    $fieldOptions + [
                        'choices' => array_combine($definition->getOptions() ?? [], $definition->getOptions() ?? []),
                        'placeholder' => 'Select...',
                    ],
                ),
            };
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('entity_type');
        $resolver->setAllowedTypes('entity_type', 'string');
    }
}
