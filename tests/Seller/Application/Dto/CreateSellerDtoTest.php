<?php

namespace GardenManager\Tests\Seller\Application\Dto;

use GardenManager\Seller\Application\Dto\CreateSellerDto;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateSellerDtoTest extends KernelTestCase
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
        $dto = new CreateSellerDto();
        $dto->name = 'John Garden';
        $dto->email = 'john@example.com';

        $errors = $this->validator->validate($dto);

        self::assertCount(0, $errors);
    }

    #[Test]
    public function blankRequiredFieldsFail(): void
    {
        $dto = new CreateSellerDto();

        $errors = $this->validator->validate($dto);

        self::assertGreaterThan(0, count($errors));

        $fields = [];
        foreach ($errors as $error) {
            $fields[] = $error->getPropertyPath();
        }

        self::assertContains('name', $fields);
        self::assertContains('email', $fields);
    }

    #[Test]
    public function invalidEmailFails(): void
    {
        $dto = new CreateSellerDto();
        $dto->name = 'John';
        $dto->email = 'not-an-email';

        $errors = $this->validator->validate($dto);

        self::assertGreaterThan(0, count($errors));
        self::assertSame('email', $errors[0]->getPropertyPath());
    }

    #[Test]
    public function nameTooLongFails(): void
    {
        $dto = new CreateSellerDto();
        $dto->name = str_repeat('a', 256);
        $dto->email = 'john@example.com';

        $errors = $this->validator->validate($dto);

        self::assertGreaterThan(0, count($errors));
    }
}
