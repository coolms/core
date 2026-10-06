<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Ui;

use CoolMS\Core\Ui\ContractName;
use CoolMS\Core\Ui\HostContracts;
use CoolMS\Core\Ui\ThemeContracts;
use CoolMS\Core\Ui\UiContractMatcher;
use CoolMS\Core\Ui\UiEntry;
use CoolMS\Core\Ui\UiVerdict;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The renamed contracts (2026-10-06): `console` is `admin` and `desk` is `workspace`, and while the old names
 * are deprecated aliases a package on one name meets a package on the other, in both directions -- the case the
 * alias exists for. Without it a module still on `console` is silently UNUSED under a theme on `admin`, not refused.
 */
final class ContractNameTest extends TestCase
{
    #[Test]
    public function anOldNameIsReadAsItsNewOneAndOnlyTheTwoOldNamesAreDeprecated(): void
    {
        self::assertSame('admin', ContractName::canonical('console'));
        self::assertSame('workspace', ContractName::canonical('desk'));
        self::assertSame('site', ContractName::canonical('site'));
        self::assertSame('admin', ContractName::canonical('admin'));
        self::assertTrue(ContractName::isDeprecated('console'));
        self::assertTrue(ContractName::isDeprecated('desk'));
        self::assertFalse(ContractName::isDeprecated('admin'));
        self::assertFalse(ContractName::isDeprecated('site'));
        self::assertSame(['console'], ContractName::aliasesOf('admin'));
        self::assertSame(['desk'], ContractName::aliasesOf('workspace'));
        self::assertSame([], ContractName::aliasesOf('site'));
    }

    #[Test]
    public function aModuleOnTheOldNameMeetsAThemeOnTheNewOneAndTheReverse(): void
    {
        $old = new UiEntry('email', 'console', '^1.0', 'angular', 'ui/angular/entries/console.ts');
        self::assertSame('admin', $old->contract);
        self::assertSame('console', $old->declaredContract);

        $matcher = new UiContractMatcher();
        $onNew = $matcher->match(new ThemeContracts('coolms-admin', 'angular', ['admin' => '1.0']), [$old]);
        self::assertSame(UiVerdict::Matched, $onNew[0]->verdict);
        self::assertSame(
            "Module 'email' offers admin ^1.0 for angular; theme 'coolms-admin' implements admin 1.0 -- matched.",
            $onNew[0]->reason,
        );

        $new = new UiEntry('email', 'admin', '^1.0', 'angular', 'ui/angular/entries/admin.ts');
        self::assertSame('admin', $new->declaredContract, 'a new name is declared as itself');
        $onOld = $matcher->match(new ThemeContracts('coolms-admin', 'angular', ['console' => '1.0']), [$new]);
        self::assertSame(UiVerdict::Matched, $onOld[0]->verdict);

        // The range still decides across names: the alias renames, it does not admit.
        $later = new UiEntry('call', 'desk', '^2.0', 'angular', 'x');
        $refused = $matcher->match(new ThemeContracts('app', 'angular', ['workspace' => '1.0']), [$later]);
        self::assertSame(UiVerdict::Refused, $refused[0]->verdict);
    }

    #[Test]
    public function aThemeHoldsItsContractsUnderTheNewNamesAndRemembersWhatItWrote(): void
    {
        $theme = new ThemeContracts('coolms-admin', 'angular', ['console' => '1.0', 'site' => '1.1']);

        self::assertSame(['admin' => '1.0', 'site' => '1.1'], $theme->versions);
        self::assertSame(['admin' => 'console', 'site' => 'site'], $theme->declaredNames);
        self::assertTrue($theme->declares('admin'));
        self::assertTrue($theme->declares('console'));
        self::assertSame('1.0', $theme->versionOf('console'));
        self::assertSame('1.0', $theme->versionOf('admin'));
    }

    #[Test]
    public function aThemeDeclaringOneContractUnderBothNamesIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Theme 'coolms-admin' declares console and admin, one contract under two names; declare admin alone.",
        );
        new ThemeContracts('coolms-admin', 'angular', ['console' => '1.0', 'admin' => '1.0']);
    }

    #[Test]
    public function theOtherRenamedContractUnderBothNamesIsRefusedToo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Theme 'app' declares workspace and desk, one contract under two names; declare workspace alone.",
        );
        new ThemeContracts('app', 'angular', ['workspace' => '1.0', 'desk' => '1.0']);
    }

    #[Test]
    public function anOldNameHasNoAliasesAndNamesAreReadExactly(): void
    {
        self::assertSame([], ContractName::aliasesOf('console'), 'aliases are of the new name, not the old');
        // Names are read as written: `Console` is not `console`, so it is no alias and nothing renames it.
        self::assertSame('Console', ContractName::canonical('Console'));
        self::assertFalse(ContractName::isDeprecated('Console'));
        self::assertSame(['Console' => '1.0'], new ThemeContracts('t', 'angular', ['Console' => '1.0'])->versions);
    }

    #[Test]
    public function twoThemesForOneContractAreRefusedWhicheverNameComesFirst(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Themes 'new-admin' and 'coolms-admin' both implement admin; an installation has one theme per host contract.",
        );
        HostContracts::fromThemes([
            new ThemeContracts('new-admin', 'angular', ['admin' => '1.0']),
            new ThemeContracts('coolms-admin', 'angular', ['console' => '1.0']),
        ]);
    }

    #[Test]
    public function twoThemesForOneContractAreRefusedUnderEitherName(): void
    {
        $old = new ThemeContracts('coolms-admin', 'angular', ['console' => '1.0']);
        $hosts = HostContracts::fromThemes([$old]);
        self::assertSame(['admin' => '1.0'], $hosts->versions());
        self::assertSame(['admin' => 'coolms-admin'], $hosts->hosts());
        self::assertSame($old, $hosts->forContract('console'));
        self::assertSame($old, $hosts->forContract('admin'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Themes 'coolms-admin' and 'other-admin' both implement admin; an installation has one theme per host contract.",
        );
        HostContracts::fromThemes([$old, new ThemeContracts('other-admin', 'angular', ['admin' => '1.0'])]);
    }
}
