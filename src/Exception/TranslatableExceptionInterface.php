<?php

declare(strict_types=1);

namespace CoolMS\Core\Exception;

use Throwable;

/**
 * Marks an exception whose user-facing message can be localized. F6
 * Phase 3.
 *
 * The exception still carries a raw `getMessage()` -- that stays the
 * developer-facing string and the log line (always in the source
 * language, never per-user). This interface adds a parallel,
 * translatable representation: a catalogue key + params + domain that
 * the exception renderer resolves against the request locale (or the
 * platform default in non-request contexts).
 *
 * **Why a key, not the message.** Translating `getMessage()` directly
 * would mean keying catalogues by free-form English sentences -- brittle
 * and unsearchable. A stable key (`errors.workflow.not_forkable`) is the
 * trans-unit id; the raw message is the fallback when no catalogue entry
 * exists for the locale.
 *
 * **Rendering contract** (see `UnhandledExceptionListener`): the renderer
 * calls `translator->trans(key, params, domain, locale)`. If the
 * translator returns the key unchanged (no entry), it falls back to
 * `getMessage()`. Translation failures never propagate -- an exception
 * thrown while rendering an exception would mask the original.
 *
 * Implementations typically `use TranslatableExceptionTrait` and call
 * `setTranslation()` from a named constructor.
 */
interface TranslatableExceptionInterface extends Throwable
{
    /**
     * Stable catalogue key, e.g. `errors.workflow.not_forkable`.
     */
    public function getTranslationKey(): string;

    /**
     * Placeholder substitutions for the catalogue message, e.g.
     * `['%key%' => 'identity.verify_new_user']`.
     *
     * @return array<string, mixed>
     */
    public function getTranslationParameters(): array;

    /**
     * Translation domain (catalogue file stem), or null for Symfony's
     * default `messages` domain. A dedicated `exceptions` domain keeps
     * error strings out of the general UI catalogue.
     */
    public function getTranslationDomain(): ?string;
}
