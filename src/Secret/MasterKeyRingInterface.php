<?php

declare(strict_types=1);

namespace CoolMS\Core\Secret;

/**
 * The master keys a host holds, in the order a reader tries them.
 *
 * Writes use the CURRENT key, always. Reads try the current key first and
 * then the previous one, so a value sealed before a rotation stays readable
 * for as long as both keys are held -- which is what makes a rotation a
 * window rather than an outage: every kind of sealed value is readable in a
 * mixed state, because the box authenticates and opening under the wrong key
 * fails deterministically rather than yielding garbage.
 *
 * The ring says nothing about how long the previous key is held. Retiring it
 * from the runtime is an operator's act, taken after every kind reports zero
 * values under it; retiring it from the vault is a separate decision that
 * depends on which backups must stay restorable.
 */
interface MasterKeyRingInterface
{
    /** True when a current key is present and well-formed; the previous key does not count. */
    public function isConfigured(): bool;

    /**
     * The key every new value is sealed under.
     *
     * @throws MasterKeyException when no usable current key is configured
     */
    public function current(): MasterKey;

    /**
     * Every key a reader may try, the current key first. Empty when nothing
     * is configured; never contains the same key twice.
     *
     * @return list<MasterKey>
     */
    public function keys(): array;

    /** The key with this id, if the ring holds it. */
    public function byId(string $id): ?MasterKey;
}
