<?php

declare(strict_types=1);

namespace GardenManager\Tests\Architecture;

use FilesystemIterator;
use GardenManager\Permission\Domain\PermissionProviderInterface;
use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use GardenManager\Shared\Application\QueryInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

#[Group('unit')]
final class MessageAuthorizationTest extends TestCase
{
    /** @var list<class-string> */
    private static array $sourceClasses = [];

    #[Test]
    public function everyMessageDeclaresExactlyOneAuthorizationPolicy(): void
    {
        $violations = [];

        foreach (self::messageClasses() as $class) {
            $reflection = new ReflectionClass($class);
            $hasRequirement = $reflection->getAttributes(RequiresPermission::class) !== [];
            $hasExemption = $reflection->getAttributes(NoPermissionRequired::class) !== [];

            if ($hasRequirement && $hasExemption) {
                $violations[] = \sprintf('%s declares both #[RequiresPermission] and #[NoPermissionRequired]; remove one of them.', $class);
            } elseif (!$hasRequirement && !$hasExemption) {
                $violations[] = \sprintf('%s declares neither #[RequiresPermission] nor #[NoPermissionRequired]; every command/query must declare its authorization policy.', $class);
            } elseif ($hasRequirement && !$reflection->implementsInterface(AuthorizedMessageInterface::class)) {
                $violations[] = \sprintf('%s has #[RequiresPermission] but does not implement AuthorizedMessageInterface, so the actor and tenant cannot be resolved.', $class);
            }
        }

        self::assertSame([], $violations, "Message authorization policy violations:\n- " . implode("\n- ", $violations));
    }

    #[Test]
    public function everyExemptionDocumentsANonEmptyReason(): void
    {
        $violations = [];

        foreach (self::messageClasses() as $class) {
            foreach (new ReflectionClass($class)->getAttributes(NoPermissionRequired::class) as $attribute) {
                try {
                    $attribute->newInstance();
                } catch (InvalidArgumentException) {
                    $violations[] = \sprintf('%s must document a non-empty exemption reason.', $class);
                }
            }
        }

        self::assertSame([], $violations, "Exemptions without a valid reason:\n- " . implode("\n- ", $violations));
    }

    #[Test]
    public function everyRequiredPermissionIsRegisteredByAProvider(): void
    {
        $registered = self::registeredPermissions();
        $violations = [];

        foreach (self::messageClasses() as $class) {
            foreach (new ReflectionClass($class)->getAttributes(RequiresPermission::class) as $attribute) {
                $permission = $attribute->newInstance()->permission;

                if (!\in_array($permission, $registered, true)) {
                    $violations[] = \sprintf('%s requires "%s", which no PermissionProviderInterface implementation registers.', $class, $permission);
                }
            }
        }

        self::assertSame([], $violations, "Unregistered permissions:\n- " . implode("\n- ", $violations));
    }

    /**
     * @return list<class-string>
     */
    private static function messageClasses(): array
    {
        $classes = array_values(array_filter(
            self::sourceClasses(),
            static fn (string $class): bool => is_subclass_of($class, CommandInterface::class)
                || is_subclass_of($class, QueryInterface::class),
        ));

        self::assertNotEmpty($classes, 'No command/query message classes were discovered under src/; the class scan is broken.');

        return $classes;
    }

    /**
     * @return list<string>
     */
    private static function registeredPermissions(): array
    {
        $permissionSets = [];

        foreach (self::sourceClasses() as $class) {
            if (!is_subclass_of($class, PermissionProviderInterface::class)) {
                continue;
            }

            $constructor = new ReflectionClass($class)->getConstructor();
            self::assertTrue(
                $constructor === null || $constructor->getNumberOfRequiredParameters() === 0,
                \sprintf('%s has required constructor parameters; this test instantiates providers directly.', $class),
            );

            $provider = new $class();
            $permissionSets[] = array_keys($provider->getPermissions());
        }

        self::assertNotEmpty($permissionSets, 'No permission providers were discovered under src/; the class scan is broken.');

        return array_merge(...$permissionSets);
    }

    /**
     * @return list<class-string>
     */
    private static function sourceClasses(): array
    {
        if (self::$sourceClasses !== []) {
            return self::$sourceClasses;
        }

        $srcDir = \dirname(__DIR__, 2) . '/src';
        $classes = [];

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = substr($file->getPathname(), \strlen($srcDir) + 1, -\strlen('.php'));
            $fqcn = 'GardenManager\\' . str_replace('/', '\\', $relativePath);

            if (!class_exists($fqcn)) {
                continue;
            }

            if (new ReflectionClass($fqcn)->isAbstract()) {
                continue;
            }

            $classes[] = $fqcn;
        }

        sort($classes);

        return self::$sourceClasses = $classes;
    }
}
