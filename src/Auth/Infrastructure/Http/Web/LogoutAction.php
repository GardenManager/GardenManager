<?php

namespace GardenManager\Auth\Infrastructure\Http\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutAction extends AbstractController
{
    #[Route('/logout', name: 'app_logout')]
    public function __invoke(): never
    {
        throw new \LogicException('This method should be intercepted by the firewall.');
    }
}
