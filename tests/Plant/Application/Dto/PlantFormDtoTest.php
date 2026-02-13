<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Application\Dto;

use GardenManager\Plant\Application\Dto\PlantFormDto;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PlantFormDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    #[Test]
    public function validDtoPassesValidation(): void
    {
        $dto = new PlantFormDto();
        $dto->localName = 'Tomato';
        $dto->isHybrid = false;
        $dto->lifecycle = LifecycleEnum::ANNUAL;

        $errors = $this->validator->validate($dto);

        self::assertCount(0, $errors);
    }

    #[Test]
    public function blankRequiredFieldsFail(): void
    {
        $dto = new PlantFormDto();

        $errors = $this->validator->validate($dto);

        self::assertGreaterThan(0, \count($errors));

        $fields = [];
        foreach ($errors as $error) {
            $fields[] = $error->getPropertyPath();
        }

        self::assertContains('localName', $fields);
    }

    #[Test]
    public function localNameTooLongFails(): void
    {
        $dto = new PlantFormDto();
        $dto->localName = str_repeat('a', 256);
        $dto->isHybrid = false;
        $dto->lifecycle = LifecycleEnum::ANNUAL;

        $errors = $this->validator->validate($dto);

        self::assertGreaterThan(0, \count($errors));
    }
}
