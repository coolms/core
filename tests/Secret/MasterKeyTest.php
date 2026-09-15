<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Secret;

use CoolMS\Core\Secret\MasterKey;
use CoolMS\Core\Secret\MasterKeyException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function base64_encode;
use function random_bytes;
use function str_repeat;
use function strlen;

final class MasterKeyTest extends TestCase
{
    #[Test]
    public function theIdIsDerivedFromTheBytesAloneSoEveryHostAgreesOnIt(): void
    {
        $bytes = random_bytes(32);

        $a = new MasterKey($bytes);
        $b = MasterKey::fromBase64(base64_encode($bytes));

        self::assertSame(16, strlen($a->id));
        self::assertSame($a->id, $b->id);
        self::assertTrue($a->is($b));
        self::assertSame($bytes, $b->bytes());
    }

    #[Test]
    public function twoKeysHaveTwoIds(): void
    {
        self::assertFalse(new MasterKey(random_bytes(32))->is(new MasterKey(random_bytes(32))));
    }

    #[Test]
    public function theWrongLengthIsRefusedByName(): void
    {
        $this->expectException(MasterKeyException::class);
        $this->expectExceptionMessage('must decode to 32 bytes, got 16');

        new MasterKey(str_repeat('k', 16));
    }

    #[Test]
    public function somethingThatIsNotBase64IsRefusedByName(): void
    {
        $this->expectException(MasterKeyException::class);
        $this->expectExceptionMessage('not valid base64');

        MasterKey::fromBase64('not base64!');
    }
}
