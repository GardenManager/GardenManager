<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class UpdatePlantAction extends AbstractController
{
    public function __construct(
        private CommandDispatcher $commandDispatcher,
        private QueryDispatcher $queryDispatcher,
    ) {
    }

    #[Route(
        path: '/plants/{id}/update',
        name: 'plant_update',
        requirements: ['id' => '[0-9A-Z]{26}'],
        methods: ['GET', 'POST']
    )]
    public function __invoke(): Response
    {

    }
}
