<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Analytics;

use CoolMS\Core\Analytics\ConsentCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The tiered-consent vocabulary: the canonical category slugs + order, and
 * the one entailment -- recognising a person implies measuring the audience.
 */
#[CoversClass(ConsentCategory::class)]
final class ConsentCategoryTest extends TestCase
{
    #[Test]
    public function itExposesTheFiveSlugsInCanonicalOrder(): void
    {
        self::assertSame(
            ['necessary', 'analytics', 'recognition', 'personalization', 'marketing'],
            ConsentCategory::slugs(),
        );
    }

    #[Test]
    public function eachCaseIsBackedByItsSlug(): void
    {
        self::assertSame('necessary', ConsentCategory::Necessary->value);
        self::assertSame('recognition', ConsentCategory::Recognition->value);
        self::assertSame('personalization', ConsentCategory::Personalization->value);
        self::assertSame(ConsentCategory::Marketing, ConsentCategory::from('marketing'));
    }

    #[Test]
    public function recognisingImpliesMeasuringAndNothingElseImpliesAnything(): void
    {
        self::assertSame([ConsentCategory::Analytics], ConsentCategory::Recognition->implies());
        foreach ([ConsentCategory::Necessary, ConsentCategory::Analytics, ConsentCategory::Personalization, ConsentCategory::Marketing] as $category) {
            self::assertSame([], $category->implies(), $category->value . ' entails nothing');
        }
    }

    #[Test]
    public function theClosureMakesProfileWithoutMeasuringUnrepresentable(): void
    {
        self::assertSame(['necessary', 'analytics', 'recognition'], ConsentCategory::closure(['recognition']));
        self::assertSame(['necessary', 'analytics', 'recognition'], ConsentCategory::closure(['recognition', 'analytics']));
        self::assertSame(['necessary', 'analytics'], ConsentCategory::closure(['analytics']), 'measuring alone does not recognise');
    }

    #[Test]
    public function theClosureCanonicalisesOrderAddsNecessaryAndDropsUnknownSlugs(): void
    {
        self::assertSame(['necessary'], ConsentCategory::closure([]));
        self::assertSame(['necessary', 'analytics', 'marketing'], ConsentCategory::closure(['marketing', 'bogus', 'analytics']));
        self::assertSame(['necessary'], ConsentCategory::closure(['accepted']), 'the legacy binary is not a slug');
    }
}
