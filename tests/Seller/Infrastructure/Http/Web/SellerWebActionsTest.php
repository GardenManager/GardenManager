<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Infrastructure\Http\Web;

use Doctrine\ORM\EntityManagerInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SellerWebActionsTest extends WebTestCase
{
    #[Test]
    public function indexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sellers');

        self::assertResponseRedirects();
    }

    #[Test]
    public function indexRendersForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/sellers');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My Sellers');
    }

    #[Test]
    public function createSellerFlow(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/sellers/new');
        self::assertResponseIsSuccessful();

        $client->submitForm('Create Seller', [
            'seller_form[name]' => 'Test Seller',
            'seller_form[email]' => 'seller@test.com',
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function editSellerFlow(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $seller = $this->createSeller($user);

        $client->request('GET', '/sellers/' . $seller->getId() . '/edit');
        self::assertResponseIsSuccessful();

        $client->submitForm('Save Changes', [
            'seller_form[name]' => 'Updated Name',
            'seller_form[email]' => 'updated@test.com',
        ]);

        self::assertResponseRedirects();
    }

    #[Test]
    public function deleteSellerFlow(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $seller = $this->createSeller($user);
        $id = (string) $seller->getId();

        $crawler = $client->request('GET', '/sellers/' . $id);
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('#delete-modal form[action]')->form();
        $client->submit($form);
        self::assertResponseRedirects('/sellers');
    }

    #[Test]
    public function cannotAccessOtherUsersSeller(): void
    {
        $client = static::createClient();

        $owner = $this->createUser();
        $seller = $this->createSeller($owner);

        $otherUser = $this->createUser();
        $client->loginUser($otherUser);

        $client->request('GET', '/sellers/' . $seller->getId());
        self::assertResponseStatusCodeSame(403);
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
