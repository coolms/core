<?php

declare(strict_types=1);
namespace CoolMS\Core\VFS;

/**
 * One kind of file a module can create, offered wherever the platform lets
 * somebody make a new file (#2056).
 *
 * ## Why a catalogue and not a create() method
 *
 * The obvious design is a contributor with `create(folder, name)` on it, and it
 * is the wrong one. Every module that can mint a file ALREADY has an endpoint
 * for it, carrying rules a generic VFS create cannot: server-side slugging with
 * national transliteration, per-format seed bytes, mime stamping, conflict 409s,
 * `contextSchema` extraction, per-space roots. Routing creation through VFS
 * would mean either duplicating all of that or reducing every module to
 * "touch an empty file", which is how `.dsheet` documents were born broken
 * (#2054).
 *
 * So this describes WHAT can be created and HOW TO ASK for it. The module keeps
 * ownership of the act; VFS only knows the menu. A second creation path is
 * exactly what this avoids.
 *
 * ## Why the field names travel with the kind
 *
 * `POST /document/documents` takes `title`; `POST /document/templates` takes
 * `name`. Both are right for their own resource and neither is going to be
 * renamed to satisfy a menu. Naming the field here keeps the client generic
 * without forcing a rename or a translation layer that would have to be updated
 * every time a module ships a new kind.
 */
final readonly class CreatableFileKind
{
    /**
     * @param string               $id          stable, module-prefixed, e.g. `document.word-template`
     * @param string               $label       what the operator picks, e.g. "Word template"
     * @param string               $group       menu grouping, e.g. "Documents"; kinds from one module belong together
     * @param string               $icon        Bootstrap icon class, e.g. `bi-file-earmark-word`
     * @param string               $endpoint    API path the client POSTs to
     * @param string               $nameField   body field carrying the typed name (`title`, `name`, …)
     * @param string               $folderField body field carrying the destination folder
     * @param array<string, mixed> $payload     fixed fields merged into the body, e.g. `['format' => 'word']`
     * @param string|null          $extension   shown as a hint; the SERVER still decides the real filename
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $group,
        public string $icon,
        public string $endpoint,
        public string $nameField = 'name',
        public string $folderField = 'folderPath',
        public array $payload = [],
        public ?string $extension = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'group' => $this->group,
            'icon' => $this->icon,
            'endpoint' => $this->endpoint,
            'nameField' => $this->nameField,
            'folderField' => $this->folderField,
            // Cast so an EMPTY payload encodes as `{}` and not `[]` (ADR-157) —
            // a client that spreads it into a request body cannot use an array.
            'payload' => (object) $this->payload,
            'extension' => $this->extension,
        ];
    }
}
