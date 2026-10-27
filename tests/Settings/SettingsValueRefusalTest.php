<?php

declare(strict_types=1);

namespace CoolMS\Core\Tests\Settings;

use CoolMS\Core\Settings\SettingsValueCheckInterface;
use CoolMS\Core\Settings\SettingsValueRefusal;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A refusal names the field it is about, carries a code a client can branch on, and says why to the admin; a check
 * answers refusals for the whole block it is given.
 */
final class SettingsValueRefusalTest extends TestCase
{
    /** @return iterable<string, array{string, string, string}> */
    public static function malformed(): iterable
    {
        yield 'no field' => [' ', 'identifier.no_channel', 'Phone needs an SMS channel.'];
        yield 'no reason' => ['phone', 'identifier.no_channel', ''];
        yield 'an empty code' => ['phone', '', 'Phone needs an SMS channel.'];
        yield 'a capital in the code' => ['phone', 'Identifier.no_channel', 'Phone needs an SMS channel.'];
        yield 'a space in the code' => ['phone', 'no channel', 'Phone needs an SMS channel.'];
        yield 'a trailing dot' => ['phone', 'identifier.', 'Phone needs an SMS channel.'];
    }

    #[Test]
    public function aRefusalCarriesItsFieldCodeAndReason(): void
    {
        $refusal = new SettingsValueRefusal('phone', 'identifier.no_confirmation_channel', 'Phone needs an SMS channel to send its codes.');

        self::assertSame('phone', $refusal->field);
        self::assertSame('identifier.no_confirmation_channel', $refusal->code);
        self::assertSame('Phone needs an SMS channel to send its codes.', $refusal->reason);
    }

    #[Test]
    #[DataProvider('malformed')]
    public function aMalformedRefusalIsRefused(string $field, string $code, string $reason): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SettingsValueRefusal($field, $code, $reason);
    }

    #[Test]
    public function aCheckRelatesOneKeyToAnotherInTheWholeBlock(): void
    {
        $check = new class implements SettingsValueCheckInterface {
            public function checks(): array
            {
                return ['identity.identifiers'];
            }

            public function check(string $key, array $values, ?string $scope = null): array
            {
                return true === ($values['phone'] ?? false) && true !== ($values['sms'] ?? false)
                    ? [new SettingsValueRefusal('phone', 'identifier.no_confirmation_channel', 'Phone needs an SMS channel.')]
                    : [];
            }
        };

        self::assertSame(['identity.identifiers'], $check->checks());
        self::assertSame([], $check->check('identity.identifiers', ['phone' => true, 'sms' => true]));
        $refusals = $check->check('identity.identifiers', ['phone' => true, 'sms' => false]);
        self::assertCount(1, $refusals);
        self::assertSame('phone', $refusals[0]->field);
    }
}
