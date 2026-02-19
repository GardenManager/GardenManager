<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Infrastructure\Http\Api;

use Doctrine\ORM\EntityManagerInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('functional')]
final class SellerApiActionsTest extends WebTestCase
{
    #[Test]
    public function listSellersReturnsJson(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/api/sellers');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        self::assertArrayHasKey('data', $data);
        self::assertArrayHasKey('pagination', $data);
        self::assertIsArray($data['data']);
        self::assertArrayHasKey('page', $data['pagination']);
        self::assertArrayHasKey('limit', $data['pagination']);
        self::assertArrayHasKey('total', $data['pagination']);
        self::assertArrayHasKey('pages', $data['pagination']);
    }

    #[Test]
    public function createSellerReturns201(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('POST', '/api/sellers', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'API Seller',
            'email' => 'api@test.com',
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('API Seller', $data['name']);
        self::assertSame('api@test.com', $data['email']);
    }

    #[Test]
    public function createWithInvalidDataReturns422(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('POST', '/api/sellers', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'name' => '',
            'email' => 'not-an-email',
        ]));

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('violations', $data);
    }

    #[Test]
    public function updateSellerReturns200(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $seller = $this->createSeller($user);

        $client->request('PUT', '/api/sellers/' . $seller->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Updated API',
            'email' => 'updated@test.com',
        ]));

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('Updated API', $data['name']);
    }

    #[Test]
    public function deleteSellerReturns204(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $seller = $this->createSeller($user);

        $client->request('DELETE', '/api/sellers/' . $seller->getId());

        self::assertResponseStatusCodeSame(204);
    }

    #[Test]
    public function cannotAccessOtherUsersSeller(): void
    {
        $client = static::createClient();

        $owner = $this->createUser();
        $seller = $this->createSeller($owner);

        $otherUser = $this->createUser();
        $client->loginUser($otherUser);

        $client->request('GET', '/api/sellers/' . $seller->getId());
        self::assertResponseStatusCodeSame(403);
    }

    #[Test]
    public function deletedSellerReturns404(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $seller = $this->createSeller($user);
        $seller->softDelete();
        $repo = self::getContainer()->get(SellerRepositoryInterface::class);
        $repo->save($seller);
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $client->request('GET', '/api/sellers/' . $seller->getId());
        self::assertResponseStatusCodeSame(404);
    }

    private function createUser(): AuthUser
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $user = AuthUser::createWithPassword(
            new \Symfony\Component\Uid\Ulid(),
            'user-' . bin2hex(random_bytes(4)) . '@test.com',
            'Test User',
            'hashed',
        );

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createSeller(AuthUser $owner): Seller
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $seller = Seller::create(name: 'Test Seller', email: 'seller@test.com', ownerId: $owner->getId());

        $repository = self::getContainer()->get(SellerRepositoryInterface::class);
        $repository->save($seller);
        $em->flush();

        return $seller;
    }
}
