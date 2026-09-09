<?php

declare(strict_types=1);
namespace CoolMS\Core\Editor;

/**
 * Unparsed profile descriptor as a loader sees it. Carries the verbatim
 * YAML fields so the resolver can apply `extends:` inheritance and the
 * `+`/`-` directive grammar after every loader has chimed in.
 *
 * The `contributors` and `allowedWidgets` fields hold the raw entries
 * exactly as the YAML produced them: each entry is either a plain string
 * id, a `+id` (add-to-inherited) directive, a `-id` (remove-from-inherited)
 * directive, the literal '*' (wildcard), or null when the YAML key was
 * absent (interpreted by the resolver as "no override at this loader").
 *
 *   contributors    null | string[] | '*' (string token, normalised to
 *                   ['*'] by the resolver)
 *   allowedWidgets  null | string[] | '*'
 */
final readonly class RawProfileDescriptor
{
    /**
     * @param array<string>|string|null $contributors
     * @param array<string>|string|null $allowedWidgets
     */
    public function __construct(
        public string $name,
        public ?string $extends,
        public array|string|null $contributors,
        public array|string|null $allowedWidgets,
        /** Origin label for error messages (file path, "module" tag, etc.). */
        public string $origin = '',
    ) {
    }
}
