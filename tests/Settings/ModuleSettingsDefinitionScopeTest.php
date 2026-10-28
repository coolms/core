<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Settings;

use CoolMS\Core\Settings\ModuleSettingsDefinition;
use CoolMS\Core\Settings\SettingsScope;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModuleSettingsDefinitionScopeTest extends TestCase
{
    #[Test]
    public function aBlockThatSaysNothingIsProjectAsEveryBlockWasBefore(): void
    {
        $definition = new ModuleSettingsDefinition(key: 'web.public_widgets', module: 'web', label: 'Widgets');

        self::assertSame(SettingsScope::Project, $definition->scopeOf('cookie_consent'));
        self::assertFalse($definition->isInstallationOnly());
    }

    #[Test]
    public function aKeyNamedInScopesTakesItsOwnScope(): void
    {
        $definition = new ModuleSettingsDefinition(
            key: 'mixed.block',
            module: 'mixed',
            label: 'Mixed',
            scopes: ['server_name' => SettingsScope::Installation],
        );

        self::assertSame(SettingsScope::Installation, $definition->scopeOf('server_name'));
        self::assertSame(SettingsScope::Project, $definition->scopeOf('theme'));
        self::assertFalse($definition->isInstallationOnly());
    }

    #[Test]
    public function aPinnedKeyIsInstallationWhateverTheBlocksDefault(): void
    {
        $definition = new ModuleSettingsDefinition(
            key: 'web.page_cache',
            module: 'web',
            label: 'Page cache',
            envPins: ['ttl' => 'PAGE_CACHE_TTL'],
        );

        self::assertSame(SettingsScope::Installation, $definition->scopeOf('ttl'));
        self::assertSame(SettingsScope::Project, $definition->scopeOf('enabled'));
    }

    #[Test]
    public function aPinnedKeyDeclaredProjectIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"base_url" is pinned by COOLMS_APPLICATION_BASE_URL');

        new ModuleSettingsDefinition(
            key: 'section.application',
            module: 'section',
            label: 'Application',
            envPins: ['base_url' => 'COOLMS_APPLICATION_BASE_URL'],
            scopes: ['base_url' => SettingsScope::Project],
        );
    }

    #[Test]
    public function aBlockIsInstallationOnlyWhenItsDefaultIsAndNoKeyIsProject(): void
    {
        $all = new ModuleSettingsDefinition(
            key: 'email.system_mail',
            module: 'email',
            label: 'System mail',
            defaultScope: SettingsScope::Installation,
        );
        $exception = new ModuleSettingsDefinition(
            key: 'email.system_mail',
            module: 'email',
            label: 'System mail',
            defaultScope: SettingsScope::Installation,
            scopes: ['from_name' => SettingsScope::Project],
        );

        self::assertTrue($all->isInstallationOnly());
        self::assertSame(SettingsScope::Installation, $all->scopeOf('mailbox'));
        self::assertFalse($exception->isInstallationOnly());
        self::assertSame(SettingsScope::Project, $exception->scopeOf('from_name'));
    }
}
