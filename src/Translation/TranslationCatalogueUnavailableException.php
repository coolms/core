<?php

declare(strict_types=1);

namespace CoolMS\Core\Translation;

use RuntimeException;

/**
 * Thrown by {@see \CoolMS\CoreModule\Translation\NullInlineLabelCatalogueWriter}
 * when something tries to AUTHOR a per-locale translation but no translation
 * catalogue is wired — i.e. the I18n module (`coolms/i18n`) is not installed.
 *
 * The translation seam is deliberately optional: the read/resolve path
 * ({@see LabelResolverInterface}) degrades to the source value without I18n,
 * and {@see InlineLabelCatalogueReaderInterface} returns no overrides. Only an
 * actual WRITE is unsupported — and a loud failure here is correct, because
 * silently dropping authored translations would lose operator input. In a
 * single-locale deployment the authoring UI never surfaces (no non-default
 * locales), so this is only reachable by a direct API call carrying a
 * translation payload against an I18n-less build.
 */
final class TranslationCatalogueUnavailableException extends RuntimeException
{
    public static function forWrite(): self
    {
        return new self(
            'Cannot author translations: no translation catalogue is available. '
            . 'Install the I18n module (coolms/i18n) to enable per-locale label '
            . 'overrides, or omit the translation payload.',
        );
    }
}
