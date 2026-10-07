<?php

declare(strict_types=1);

namespace CoolMS\Core\Attribute;

use Attribute;

/**
 * Marks a property that holds a secret, or a value derived from one that still
 * grants or proves access: a password hash, a second-factor secret, a token or
 * its hash, a recovery or one-time code, or the ciphertext of any of these.
 *
 * Such a property is never public. The code that needs the value reads it
 * through a method of its own (for a password, the security system's
 * getPassword()), and nothing that walks an object's public surface -- a
 * serializer, a template engine, a dump -- ever meets it.
 *
 * The marker is what a check reads. An application's check of its entities
 * refuses a public property that carries it, as well as one whose name says it
 * holds a secret; the marker is for the secrets whose names do not say so.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Sensitive
{
}
