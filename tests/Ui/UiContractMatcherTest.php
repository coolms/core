<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Ui;

use CoolMS\Core\Ui\ThemeContracts;
use CoolMS\Core\Ui\UiContractMatcher;
use CoolMS\Core\Ui\UiEntry;
use CoolMS\Core\Ui\UiVerdict;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * The installer's refusal, by name, and the two controls that show the
 * theme's declaration is what is read: the same catalogue against a theme
 * that declares nothing refuses nothing, and against one that declares the
 * range's version matches everything.
 */
final class UiContractMatcherTest extends TestCase
{
    private const string EMAIL = 'email';

    private const string CALL = 'call';

    private const string INBOX = 'inbox';

    #[Test]
    public function aThemeAtOneZeroRefusesTheModuleThatNeedsTwoByName(): void
    {
        $theme = new ThemeContracts('coolms-admin', 'angular', ['console' => '1.0']);

        $verdicts = new UiContractMatcher()->match($theme, self::catalogue());

        self::assertSame(
            [UiVerdict::Matched, UiVerdict::Refused, UiVerdict::Unused],
            array_map(static fn ($v) => $v->verdict, $verdicts),
        );
        self::assertSame(
            "Module 'call' offers console ^2.0 for angular; theme 'coolms-admin' implements console 1.0 -- refused.",
            $verdicts[1]->reason,
        );
        self::assertSame(
            "Module 'inbox' offers desk ^1.0 for angular; theme 'coolms-admin' implements no desk -- unused.",
            $verdicts[2]->reason,
        );

        $refusals = new UiContractMatcher()->refusals($theme, self::catalogue());
        self::assertCount(1, $refusals);
        self::assertSame(self::CALL, $refusals[0]->entry->module);
    }

    #[Test]
    public function theSameCatalogueAgainstAThemeThatDeclaresNothingRefusesNothing(): void
    {
        // The line that proves the declaration is read: delete the theme's
        // `contracts:` block and the refusal above becomes "unused".
        $verdicts = new UiContractMatcher()->match(new ThemeContracts('coolms-admin', 'angular', []), self::catalogue());

        self::assertSame(
            [UiVerdict::Unused, UiVerdict::Unused, UiVerdict::Unused],
            array_map(static fn ($v) => $v->verdict, $verdicts),
        );
        self::assertSame([], new UiContractMatcher()->refusals(null, self::catalogue()), 'no active theme: nothing refused');
    }

    #[Test]
    public function theSameCatalogueAgainstAThemeTheRangeAdmitsMatches(): void
    {
        $verdicts = new UiContractMatcher()->match(
            new ThemeContracts('coolms-admin', 'angular', ['console' => '1.0']),
            self::catalogue('^1.0'),
        );

        self::assertSame(
            [UiVerdict::Matched, UiVerdict::Matched, UiVerdict::Unused],
            array_map(static fn ($v) => $v->verdict, $verdicts),
        );
    }

    #[Test]
    public function aMinorTheEntryNeedsAndTheThemeLacksIsARefusalAndAMajorAheadIsToo(): void
    {
        $matcher = new UiContractMatcher();
        $entry = [new UiEntry(self::EMAIL, 'console', '^1.2', 'angular', 'ui/angular/entries/console.ts')];

        self::assertCount(1, $matcher->refusals(new ThemeContracts('t', 'angular', ['console' => '1.1']), $entry), 'a point added in 1.2 is missing on 1.1');
        self::assertCount(0, $matcher->refusals(new ThemeContracts('t', 'angular', ['console' => '1.2']), $entry));
        self::assertCount(0, $matcher->refusals(new ThemeContracts('t', 'angular', ['console' => '1.9']), $entry));
        self::assertCount(1, $matcher->refusals(new ThemeContracts('t', 'angular', ['console' => '2.0']), $entry), 'a major changes or removes points');
    }

    #[Test]
    public function anotherFrameworkIsUnusedNotRefused(): void
    {
        $verdicts = new UiContractMatcher()->match(
            new ThemeContracts('coolms-react', 'react', ['console' => '1.0']),
            self::catalogue(),
        );

        self::assertSame(
            [UiVerdict::Unused, UiVerdict::Unused, UiVerdict::Unused],
            array_map(static fn ($v) => $v->verdict, $verdicts),
        );
        self::assertStringContainsString("theme 'coolms-react' is react -- unused", $verdicts[0]->reason);
    }

    #[Test]
    public function aRangeWithoutACaretAndAVersionThatIsNotMajorMinorAreRefusedAtConstruction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Module 'email' offers console with range '1.0'; a range is '^MAJOR.MINOR'.");
        new UiEntry(self::EMAIL, 'console', '1.0', 'angular', 'x');
    }

    #[Test]
    public function aThemeVersionThatIsNotMajorMinorIsRefusedAtConstruction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Theme 'coolms-admin' declares console '1'; a contract version is MAJOR.MINOR.");
        new ThemeContracts('coolms-admin', 'angular', ['console' => '1']);
    }

    /** @return list<UiEntry> the catalogue every case reads: one match, one refusal, one that offers nothing the theme reads */
    private static function catalogue(string $callRange = '^2.0'): array
    {
        return [
            new UiEntry(self::EMAIL, 'console', '^1.0', 'angular', 'ui/angular/entries/console.ts'),
            new UiEntry(self::CALL, 'console', $callRange, 'angular', 'ui/angular/entries/console.ts'),
            new UiEntry(self::INBOX, 'desk', '^1.0', 'angular', 'ui/angular/entries/desk.ts'),
        ];
    }
}
