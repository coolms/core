<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Secret;

use CoolMS\Core\Secret\RotationTally;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RotationTallyTest extends TestCase
{
    #[Test]
    public function theWindowIsClosedOnlyWhenNothingIsUnderThePreviousKey(): void
    {
        $noOld = new RotationTally(current: 5, unreadable: 2, plaintext: 1);
        self::assertTrue($noOld->closed(), 'unreadable and plaintext do not hold the window');
        self::assertFalse(new RotationTally(current: 5, previous: 1)->closed());
    }

    #[Test]
    public function totalCountsEveryValueReadOnceAndResealedIsNotAValue(): void
    {
        $t = new RotationTally(current: 2, previous: 3, unreadable: 1, plaintext: 4, resealed: 3);

        self::assertSame(10, $t->total());
    }

    #[Test]
    public function tallysAdd(): void
    {
        $sum = new RotationTally(current: 1, previous: 2)
            ->add(new RotationTally(previous: 1, unreadable: 1, resealed: 1));

        self::assertSame(
            [1, 3, 1, 0, 1],
            [$sum->current, $sum->previous, $sum->unreadable, $sum->plaintext, $sum->resealed],
        );
    }
}
