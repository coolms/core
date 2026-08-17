<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Analytics;

use CoolMS\Core\Analytics\ConsentCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The tiered-consent vocabulary: the canonical category slugs + order.
 */
#[CoversClass(ConsentCategory::class)]
final class ConsentCategoryTest extends TestCase
{
    #[Test]
    public function itExposesTheFourSlugsInCanonicalOrder(): void
    {
        self::assertSame(
            ['necessary', 'analytics', 'personalization', 'marketing'],
            ConsentCategory::slugs(),
        );
    }

    #[Test]
    public function eachCaseIsBackedByItsSlug(): void
    {
        self::assertSame('necessary', ConsentCategory::Necessary->value);
        self::assertSame('personalization', ConsentCategory::Personalization->value);
        self::assertSame(ConsentCategory::Marketing, ConsentCategory::from('marketing'));
    }
}
