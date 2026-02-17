<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Infrastructure\Http\Web;

use Doctrine\ORM\EntityManagerInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Ulid;

final class PlantWebActionsTest extends WebTestCase
{
    #[Test]
    public function indexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/plants');

        self::assertResponseRedirects();
    }

    #[Test]
    public function indexRendersForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/plants');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My Plants');
    }

    #[Test]
    public function createPlantFlow(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/plants/new');
        self::assertResponseIsSuccessful();

        $client->submitForm('Create Plant', [
            'plant_form[localName]' => 'Test Tomato',
            'plant_form[lifecycle]' => LifecycleEnum::ANNUAL->value,
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function editPlantFlow(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $plant = $this->createPlant($user);

        $client->request('GET', '/plants/' . $plant->getId() . '/update');
        self::assertResponseIsSuccessful();

        $client->submitForm('Save Changes', [
            'plant_form[localName]' => 'Updated Plant Name',
            'plant_form[lifecycle]' => LifecycleEnum::PERENNIAL->value,
        ]);

        self::assertResponseRedirects();
    }

    #[Test]
    public function deletePlantFlow(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $plant = $this->createPlant($user);
        $id = (string) $plant->getId();

        $crawler = $client->request('GET', '/plants/' . $id);
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('#delete-modal form[action]')->form();
        $client->submit($form);
        self::assertResponseRedirects('/plants');
    }

    #[Test]
    public function cannotAccessOtherUsersPlant(): void
    {
        $client = static::createClient();

        $owner = $this->createUser();
        $plant = $this->createPlant($owner);

        $otherUser = $this->createUser();
        $client->loginUser($otherUser);

        $client->request('GET', '/plants/' . $plant->getId());
        self::assertResponseStatusCodeSame(403);
    }

    private function createUser(): AuthUser
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $user = AuthUser::createWithPassword(
            new Ulid(),
            'user-' . bin2hex(random_bytes(4)) . '@test.com',
            'Test User',
            'hashed',
        );

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createPlant(AuthUser $owner): Plant
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $plant = Plant::create(
            ownerId: $owner->getId(),
            localName: 'Test Plant',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        $repository = self::getContainer()->get(PlantRepositoryInterface::class);
        $repository->save($plant);
        $em->flush();

        return $plant;
    }
}
