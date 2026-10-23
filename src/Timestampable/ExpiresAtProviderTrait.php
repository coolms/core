<?php

declare(strict_types=1);

namespace CoolMS\Core\Timestampable;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use DateTimeInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * An expiry, and whether it has passed.
 *
 * `isExpired` asks the GLOBAL clock, `Clock::get()`, and not the wall. What
 * uses this trait is an entity or a value object, which nothing wires, so it
 * cannot be handed a clock the way a service is; the global clock is what the
 * component provides for exactly that place. A service injects `ClockInterface`
 * as usual, and the framework's clock service reads the same global clock, so
 * one frozen instant answers both: a test that calls
 * `ClockSensitiveTrait::mockTime()` decides what every expiry here sees, and the
 * trait restores the clock after each test.
 *
 * An expiry is passed AT its instant, not after it (`<=`), and the comparison
 * is between instants, so the time zone either side is written in changes
 * nothing.
 */
trait ExpiresAtProviderTrait
{
    #[FieldMeta(private: true)]
    #[Groups(['read', 'list', 'search', 'stat'])]
    #[SerializedName('e')]
    public ?string $expiresAtAsString {
        get => isset($this->expiresAt) ? $this->expiresAt->format('c') : null;
    }

    #[FieldMeta(private: true)]
    public bool $isExpired {
        get => isset($this->expiresAt) && $this->expiresAt <= Clock::get()->now();
    }

    public function __construct(
        #[Column(type: 'datetime_immutable')]
        public DateTimeInterface $expiresAt,
    ) {
    }
}
