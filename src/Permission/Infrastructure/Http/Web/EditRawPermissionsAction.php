<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Permission\Application\Command\UpdateRawPermissionsCommand;
use GardenManager\Permission\Application\Service\PermissionConfigTransformer;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Infrastructure\Form\RawPermissionsFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EditRawPermissionsAction extends AbstractController
{
    public function __construct(
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly CommandDispatcher $commandDispatcher,
        private readonly PermissionConfigTransformer $configTransformer,
    ) {
    }

    #[Route(
        path: '/tenant/permissions/raw-edit',
        name: 'permission_raw_edit',
        methods: ['GET', 'POST'],
    )]
    public function __invoke(Request $request): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();
        $tenantId = $this->activeTenantProvider->getActiveTenantId();
        $config = $this->tenantRepository->getById($tenantId)->getPermissionsConfig();
        $configArray = $this->configTransformer->replaceUlidKeysWithEmails($config->toArray());
        $jsonContent = json_encode($configArray, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);

        $form = $this->createForm(RawPermissionsFormType::class, [
            'permissionsJson' => $jsonContent,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rawJson = $form->get('permissionsJson')->getData();

            try {
                $data = json_decode((string) $rawJson, true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw PermissionException::invalidConfig(['Invalid JSON: ' . $e->getMessage()]);
            }

            $data = $this->configTransformer->replaceEmailKeysWithUlids($data);

            $this->commandDispatcher->dispatchCommand(new UpdateRawPermissionsCommand(
                tenantId: $tenantId,
                actorUserId: $user->getId(),
                configData: $data,
            ));

            $this->addFlash('success', 'Permissions configuration saved successfully.');

            return $this->redirectToRoute('tenant_show');
        }

        return $this->render('permission/raw_edit.html.twig', [
            'form' => $form,
        ]);
    }
}
