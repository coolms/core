<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Secret;

use CoolMS\Core\Secret\ResidueTally;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The tally keeps the difference between a zero somebody counted and a zero
 * that is true by construction, because the report's whole worth is that
 * distinction.
 */
final class ResidueTallyTest extends TestCase
{
    #[Test]
    public function aCountedZeroCarriesItsDenominator(): void
    {
        $tally = ResidueTally::counted(6821, 0, 'database dumps');

        self::assertTrue($tally->closed());
        self::assertSame(6821, $tally->examined);
        self::assertTrue($tally->walked, 'this zero was measured, and the report must be able to say so');
    }

    #[Test]
    public function aStructuralNoneIsNotDressedAsACountedZero(): void
    {
        $tally = ResidueTally::noCopyKept('row versions until the table is vacuumed');

        self::assertTrue($tally->closed());
        self::assertSame(0, $tally->examined);
        self::assertFalse($tally->walked, '0 of 0 would read as a measurement, and nothing was measured');
        self::assertNotSame('', $tally->blindSpot, 'a structural none still says where a copy does survive');
    }

    #[Test]
    public function retainedCopiesLeaveItOpen(): void
    {
        $tally = ResidueTally::counted(6821, 5686, 'database dumps');

        self::assertFalse($tally->closed());
        self::assertSame(5686, $tally->retained);
    }

    /**
     * A negative count is a broken caller, not a negative number of copies.
     * Clamping keeps `closed()` from being satisfied by arithmetic.
     */
    #[Test]
    public function aNegativeCountCannotManufactureAClosedWindow(): void
    {
        self::assertSame(0, ResidueTally::counted(10, -5, 'x')->retained);
        self::assertSame(0, ResidueTally::counted(-1, 0, 'x')->examined);
    }
}
