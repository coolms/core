<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Block;

use CoolMS\Core\Block\BlockField;
use CoolMS\Core\Block\FormTexts;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A group that holds a form's text overrides says which field picks the form and which forms it may pick; it stays an
 * ordinary group for anything that does not know what form texts are.
 */
final class FormTextsTest extends TestCase
{
    private const array FORMS = ['login' => 'identity.login', 'register' => 'identity.register'];

    #[Test]
    public function theChoiceNamesItsFormAndNothingElseDoes(): void
    {
        $texts = new FormTexts('form', self::FORMS);

        self::assertSame('identity.login', $texts->formFor('login'));
        self::assertSame('identity.register', $texts->formFor('register'));
        self::assertNull($texts->formFor('identity.login'), 'a form id is not a choice');
        self::assertNull($texts->formFor('unknown'));
        self::assertNull($texts->formFor(null));
        self::assertNull($texts->formFor(['login']));
    }

    #[Test]
    public function itsItemsAreFieldLocaleLabelAndHelp(): void
    {
        $names = array_map(static fn (BlockField $f): string => $f->name, FormTexts::itemFields());

        self::assertSame([FormTexts::FIELD, FormTexts::LOCALE, FormTexts::LABEL, FormTexts::HELP], $names);
    }

    #[Test]
    public function onlyAGroupHoldsFormTexts(): void
    {
        $texts = new FormTexts('form', self::FORMS);

        self::assertTrue(new BlockField('texts', BlockField::KIND_GROUP, FormTexts::itemFields(), formTexts: $texts)->holdsFormTexts());
        self::assertFalse(new BlockField('texts', BlockField::KIND_TEXT, formTexts: $texts)->holdsFormTexts());
        self::assertFalse(new BlockField('texts', BlockField::KIND_GROUP, FormTexts::itemFields())->holdsFormTexts());
    }

    #[Test]
    public function itNeedsAChoiceFieldAndAForm(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FormTexts('form', []);
    }

    #[Test]
    public function itNeedsAChoiceFieldName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FormTexts('', self::FORMS);
    }
}
