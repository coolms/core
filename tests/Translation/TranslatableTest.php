<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Translation;

use CoolMS\Core\Translation\Translatable;
use CoolMS\Core\Translation\TranslatableMisconfigurationException;
use Error;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Domain seam for the LabelResolver / TranslatableTrait ship.
 *
 * These tests cover the PURE Domain-layer surface: attribute shape,
 * reflection contract, exception construction. The Application-layer
 * `LabelResolver` impl (which actually calls Symfony's translator) is
 * tested separately once that layer lands -- this file should NEVER
 * import anything from Symfony or a persistence library.
 *
 * Why test an attribute at all? Two reasons:
 *  - The default `fields = ['label']` is a UX commitment baked into
 *    the contract. A future "tighten the default" change would break
 *    every consumer that relied on it implicitly. The test pins it.
 *  - The `domain` derivation rule lives in the resolver, but the
 *    attribute IS the place where the explicit override is declared.
 *    Confirming reflection round-trips both the omitted + explicit
 *    forms guarantees the metadata layer is intact.
 */
final class TranslatableTest extends TestCase
{
    // ── #[Translatable] attribute shape ───────────────────────────────────────

    #[Test]
    public function defaultsToLabelField(): void
    {
        $attr = new Translatable();

        self::assertSame(['label'], $attr->fields);
        self::assertNull($attr->domain);
    }

    #[Test]
    public function acceptsExplicitFieldListAndDomain(): void
    {
        $attr = new Translatable(fields: ['label', 'description'], domain: 'dynamic_entity');

        self::assertSame(['label', 'description'], $attr->fields);
        self::assertSame('dynamic_entity', $attr->domain);
    }

    #[Test]
    public function isReadOnlyByConstruction(): void
    {
        // Property hooks / readonly enforcement: attempting to mutate
        // must throw at write time. Catches regressions where someone
        // drops the `readonly` modifier in a future refactor.
        $attr = new Translatable();

        $this->expectException(Error::class);
        /* @phpstan-ignore-next-line — runtime check of readonly */
        $attr->fields = ['x'];
    }

    #[Test]
    public function isReflectableOnTargetClass(): void
    {
        $rc = new ReflectionClass(TranslatableFixture::class);
        $attrs = $rc->getAttributes(Translatable::class);

        self::assertCount(1, $attrs);
        $instance = $attrs[0]->newInstance();
        self::assertInstanceOf(Translatable::class, $instance);
        self::assertSame(['label', 'description'], $instance->fields);
    }

    #[Test]
    public function isAbsentOnNonTranslatableTarget(): void
    {
        // A consumer that asks "is this class translatable" must rely on
        // attribute-presence as the discriminator. Make sure the
        // absence path is unambiguous.
        $rc = new ReflectionClass(PlainFixture::class);

        self::assertEmpty($rc->getAttributes(Translatable::class));
    }

    // ── TranslatableMisconfigurationException factories ───────────────────────

    #[Test]
    public function notTranslatableExceptionNamesTheClass(): void
    {
        $e = TranslatableMisconfigurationException::notTranslatable(PlainFixture::class);

        self::assertStringContainsString(PlainFixture::class, $e->getMessage());
        self::assertStringContainsString('#[Translatable]', $e->getMessage());
    }

    #[Test]
    public function fieldNotListedExceptionEnumeratesAllowedFields(): void
    {
        $e = TranslatableMisconfigurationException::fieldNotListed(
            TranslatableFixture::class,
            'subtitle',
            ['label', 'description'],
        );

        self::assertStringContainsString('subtitle', $e->getMessage());
        self::assertStringContainsString('label, description', $e->getMessage());
    }

    #[Test]
    public function fieldNotListedExceptionHandlesEmptyFieldList(): void
    {
        // Edge: a `#[Translatable(fields: [])]` on a class would never
        // be useful but the API allows it; the error message should
        // not crash on the empty list.
        $e = TranslatableMisconfigurationException::fieldNotListed(
            PlainFixture::class,
            'label',
            [],
        );

        self::assertStringContainsString('(none)', $e->getMessage());
    }

    #[Test]
    public function fieldDoesNotExistExceptionMentionsTheTypoHint(): void
    {
        $e = TranslatableMisconfigurationException::fieldDoesNotExist(
            TranslatableFixture::class,
            'lable', // typo
        );

        self::assertStringContainsString('lable', $e->getMessage());
        self::assertStringContainsString('typos', $e->getMessage());
    }

    #[Test]
    public function noIdentifierExceptionExplainsTheKeyRequirement(): void
    {
        $e = TranslatableMisconfigurationException::noIdentifier(PlainFixture::class);

        self::assertStringContainsString(PlainFixture::class, $e->getMessage());
        self::assertStringContainsString('identifier', $e->getMessage());
    }
}

// ── Fixtures (private to this test file by namespace) ─────────────────────────

#[Translatable(fields: ['label', 'description'])]
final class TranslatableFixture
{
    public function __construct(
        public string $label = '',
        public string $description = '',
    ) {
    }
}

final class PlainFixture
{
    public function __construct(public string $label = '')
    {
    }
}
