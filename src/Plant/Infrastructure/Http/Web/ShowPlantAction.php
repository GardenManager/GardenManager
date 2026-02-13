<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Plant\Application\Query\GetPlantQuery;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class ShowPlantAction extends AbstractController
{
    public function __construct(public QueryDispatcher $queryDispatcher)
    {
    }

    #[Route(
        path: '/plants/{plantId}',
        name: 'plant_show',
        methods: ['GET'],
    )]
    public function __invoke(Ulid $plantId): Response
    {
        /** @var Ulid $userId */
        $userId = $this->getUser()->getId();
        $plantView = $this->queryDispatcher->query(new GetPlantQuery($plantId, $userId));

        return $this->render('plant/show.html.twig', [
            'plant' => $plantView,
        ]);
    }
}
