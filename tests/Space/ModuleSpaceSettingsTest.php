<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Space;

use CoolMS\Core\Settings\ModuleSettingsReaderInterface;
use CoolMS\Core\Settings\UnknownSettingsKeyException;
use CoolMS\Core\Space\ModuleSpaceSettings;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Which sites a module is enabled for.
 *
 * The cases that matter are the ones where guessing would be easy: an absent
 * flag, an unreachable settings tier, and a site nobody has decided about.
 */
final class ModuleSpaceSettingsTest extends TestCase
{
    private const string KEY = 'document.spaces';

    #[Test]
    public function onlyExplicitlyEnabledSitesCount(): void
    {
        $s = $this->settings(['a' => ['enabled' => true], 'b' => ['enabled' => false]]);

        self::assertSame(['a'], $s->enabledFor(self::KEY, ['a', 'b']));
    }

    #[Test]
    public function anAbsentFlagReadsAsOff(): void
    {
        // !! A site nobody has decided about is OFF. The alternative -- treating
        // silence as consent -- is how every site ended up being offered as a
        // Documents space in the first place.
        $s = $this->settings(['a' => []]);

        self::assertFalse($s->isEnabledFor(self::KEY, 'a'));
    }

    #[Test]
    public function anUnreachableSettingsTierReadsAsOff(): void
    {
        // An undeclared block or a typo in the key must not turn a feature on
        // for a site that never asked. Failure direction is chosen, not inherited.
        //
        // !! THIS TEST ONCE PASSED FOR THE WRONG REASON. The import named a
        // namespace that does not exist (`...\Exception\UnknownSettingsKeyException`),
        // so `new` threw an Error rather than the settings exception -- and the
        // production `catch (Throwable)` swallowed that just as happily. The
        // assertion was green while exercising a broken import instead of the
        // behaviour it claims. phpstan found it; the test could not.
        //
        // It now throws the REAL class, so if that class ever moves this fails
        // to construct instead of quietly testing nothing.
        $reader = new class implements ModuleSettingsReaderInterface {
            public function effective(string $key, ?string $scope = null): array
            {
                throw new UnknownSettingsKeyException($key);
            }

            public function lockedKeys(string $key): array
            {
                return [];
            }
        };

        self::assertFalse(new ModuleSpaceSettings($reader)->isEnabledFor(self::KEY, 'a'));
    }

    #[Test]
    public function truthyStoredFormsAreAccepted(): void
    {
        // A settings row round-tripped through JSON or a form may arrive as 1 or
        // '1' rather than true; refusing those would silently disable a space
        // somebody had explicitly enabled.
        $s = $this->settings(['a' => ['enabled' => 1], 'b' => ['enabled' => '1'], 'c' => ['enabled' => 'yes']]);

        self::assertTrue($s->isEnabledFor(self::KEY, 'a'));
        self::assertTrue($s->isEnabledFor(self::KEY, 'b'));
        self::assertFalse($s->isEnabledFor(self::KEY, 'c'), 'an unrecognised value is not consent');
    }

    #[Test]
    public function availableForIsWhatAddSpaceOffers(): void
    {
        // The button is "enable this module here", so it must not offer a site
        // that already has it -- that would make it read as "show me another".
        $s = $this->settings(['a' => ['enabled' => true], 'b' => [], 'c' => ['enabled' => false]]);

        self::assertSame(['b', 'c'], $s->availableFor(self::KEY, ['a', 'b', 'c']));
    }

    #[Test]
    public function callerOrderIsPreserved(): void
    {
        // Two lists of the same sites must not disagree about their order.
        $s = $this->settings(['a' => ['enabled' => true], 'b' => ['enabled' => true]]);

        self::assertSame(['b', 'a'], $s->enabledFor(self::KEY, ['b', 'a']));
    }

    /** @param array<string, array<string, mixed>> $perSite */
    private function settings(array $perSite): ModuleSpaceSettings
    {
        $reader = new class($perSite) implements ModuleSettingsReaderInterface {
            /** @param array<string, array<string, mixed>> $perSite */
            public function __construct(private readonly array $perSite)
            {
            }

            public function effective(string $key, ?string $scope = null): array
            {
                return $this->perSite[$scope ?? ''] ?? [];
            }

            public function lockedKeys(string $key): array
            {
                return [];
            }
        };

        return new ModuleSpaceSettings($reader);
    }
}
