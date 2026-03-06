<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Query;

use GardenManager\CustomAttribute\Application\View\AttributeValueView;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeValueRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetAttributeValuesQueryHandler
{
    public function __construct(
        private CustomAttributeValueRepositoryInterface $valueRepository,
    ) {
    }

    /** @return list<AttributeValueView> */
    public function __invoke(GetAttributeValuesQuery $query): array
    {
        $values = $this->valueRepository->findByEntityTypeAndEntityId(
            $query->entityType,
            $query->entityId,
        );

        $views = array_map(
            AttributeValueView::fromValue(...),
            $values,
        );

        usort(
            $views,
            static fn (AttributeValueView $a, AttributeValueView $b): int => $a->sortOrder <=> $b->sortOrder,
        );

        return $views;
    }
}
