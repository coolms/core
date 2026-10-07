<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Attribute;

use Attribute;
use CoolMS\Core\Attribute\Sensitive;
use Error;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

final class SensitiveTest extends TestCase
{
    #[Test]
    public function aCheckFindsTheMarkerOnAPrivateProperty(): void
    {
        $holder = new class('hash') {
            public function __construct(
                #[Sensitive]
                private string $passwordHash,
            ) {
            }

            public function passwordHash(): string
            {
                return $this->passwordHash;
            }
        };

        $property = new ReflectionProperty($holder, 'passwordHash');
        $found = $property->getAttributes(Sensitive::class);

        self::assertCount(1, $found);
        self::assertInstanceOf(Sensitive::class, $found[0]->newInstance());
        self::assertTrue($property->isPrivate());
        self::assertSame('hash', $holder->passwordHash());
    }

    #[Test]
    public function itMarksPropertiesAndNothingElse(): void
    {
        $declared = new ReflectionClass(Sensitive::class)->getAttributes(Attribute::class);

        self::assertCount(1, $declared);
        self::assertSame(Attribute::TARGET_PROPERTY, $declared[0]->newInstance()->flags);

        $onAClass = new ReflectionClass(new #[Sensitive] class {})->getAttributes(Sensitive::class);
        self::assertCount(1, $onAClass);
        $this->expectException(Error::class);
        $onAClass[0]->newInstance();
    }
}
