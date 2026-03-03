<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Service;

use GardenManager\Permission\Application\Service\PermissionConfigTransformer;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class PermissionConfigTransformerTest extends TestCase
{
    private const string ULID_A = '01JMXYZ1111111111111111111';
    private const string ULID_B = '01JMXYZ2222222222222222222';

    #[Test]
    public function replaceUlidKeysWithEmails(): void
    {
        $ulidA = new Ulid(self::ULID_A);
        $ulidB = new Ulid(self::ULID_B);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByIds')->willReturn([
            self::ULID_A => new MemberUserInfoDto($ulidA, 'alice@example.com', 'Alice'),
            self::ULID_B => new MemberUserInfoDto($ulidB, 'bob@example.com', 'Bob'),
        ]);

        $transformer = new PermissionConfigTransformer($resolver);

        $result = $transformer->replaceUlidKeysWithEmails([
            'groups' => ['viewer' => ['name' => 'Viewer']],
            'userAssignments' => [self::ULID_A => ['viewer']],
            'userOverrides' => [self::ULID_B => ['+plant.view']],
        ]);

        self::assertArrayHasKey('alice@example.com', $result['userAssignments']);
        self::assertArrayNotHasKey(self::ULID_A, $result['userAssignments']);
        self::assertArrayHasKey('bob@example.com', $result['userOverrides']);
        self::assertArrayNotHasKey(self::ULID_B, $result['userOverrides']);
    }

    #[Test]
    public function replaceUlidKeysWithEmailsKeepsUnresolvedUlids(): void
    {
        $ulidA = new Ulid(self::ULID_A);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByIds')->willReturn([
            self::ULID_A => new MemberUserInfoDto($ulidA, 'alice@example.com', 'Alice'),
        ]);

        $transformer = new PermissionConfigTransformer($resolver);

        $result = $transformer->replaceUlidKeysWithEmails([
            'userAssignments' => [
                self::ULID_A => ['viewer'],
                self::ULID_B => ['editor'],
            ],
        ]);

        self::assertArrayHasKey('alice@example.com', $result['userAssignments']);
        self::assertArrayHasKey(self::ULID_B, $result['userAssignments']);
        self::assertSame(['editor'], $result['userAssignments'][self::ULID_B]);
    }

    #[Test]
    public function replaceUlidKeysWithEmailsSkipsWhenNoUlidKeys(): void
    {
        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByIds')->willReturn([]);

        $transformer = new PermissionConfigTransformer($resolver);

        $input = [
            'groups' => ['viewer' => ['name' => 'Viewer']],
        ];

        $result = $transformer->replaceUlidKeysWithEmails($input);

        self::assertSame($input, $result);
    }

    #[Test]
    public function replaceEmailKeysWithUlids(): void
    {
        $ulidA = new Ulid(self::ULID_A);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')
            ->willReturn(new MemberUserInfoDto($ulidA, 'alice@example.com', 'Alice'));

        $transformer = new PermissionConfigTransformer($resolver);

        $result = $transformer->replaceEmailKeysWithUlids([
            'groups' => ['viewer' => ['name' => 'Viewer']],
            'userAssignments' => ['alice@example.com' => ['viewer']],
            'userOverrides' => ['alice@example.com' => ['+plant.view']],
        ]);

        self::assertArrayHasKey(self::ULID_A, $result['userAssignments']);
        self::assertArrayNotHasKey('alice@example.com', $result['userAssignments']);
        self::assertArrayHasKey(self::ULID_A, $result['userOverrides']);
    }

    #[Test]
    public function replaceEmailKeysWithUlidsPassesThroughUlidKeys(): void
    {
        $ulidA = new Ulid(self::ULID_A);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')
            ->willReturn(new MemberUserInfoDto($ulidA, 'alice@example.com', 'Alice'));

        $transformer = new PermissionConfigTransformer($resolver);

        $result = $transformer->replaceEmailKeysWithUlids([
            'userAssignments' => [
                'alice@example.com' => ['viewer'],
                self::ULID_B => ['editor'],
            ],
        ]);

        self::assertArrayHasKey(self::ULID_A, $result['userAssignments']);
        self::assertArrayHasKey(self::ULID_B, $result['userAssignments']);
        self::assertSame(['editor'], $result['userAssignments'][self::ULID_B]);
    }

    #[Test]
    public function replaceEmailKeysWithUlidsThrowsOnUnresolvableEmail(): void
    {
        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')->willReturn(null);

        $transformer = new PermissionConfigTransformer($resolver);

        $this->expectException(PermissionException::class);

        $transformer->replaceEmailKeysWithUlids([
            'userAssignments' => ['unknown@example.com' => ['viewer']],
        ]);
    }
}
