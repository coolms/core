<?php

declare(strict_types=1);

namespace CoolMS\Core\Editor;

/**
 * Wire-shape value object for a single side-panel contributed to a CoolMS
 * editor instance for a given node (the page editor's right rail first;
 * reusable by any VFS-file editor).
 *
 * Lives in Core (like {@see \CoolMS\Core\Space\Space}) so any module can
 * contribute one without a sibling-module Domain dependency — the
 * contribution contract is shared. The contributor interface
 * (the editor module's panel-contributor contract)
 * stays in the Editor module since it references the VFS node.
 *
 * Modules contribute panels for the node they own a concern over — Scheduler
 * the "Schedule" panel, VFS/Editor "History", Content "Meta"/"Fields". A panel
 * only appears when its contributing module is installed (no contributor ⇒ no
 * panel), so the host editor stops hard-coding knowledge of sibling modules.
 *
 *   id        stable, namespaced key ('schedule', 'history', 'meta', 'fields').
 *   title     panel header + a11y label ('Schedule').
 *   icon      Bootstrap-icons token, no `bi-` prefix ('calendar-event').
 *   priority  rail order; 100/200/300 leaves room.
 *   kind      the frontend component key the panel registry maps to a
 *             component. Defaults to `id`; set it when several panels share
 *             one renderer.
 */
final readonly class EditorPanel
{
    public function __construct(
        public string $id,
        public string $title,
        public string $icon,
        public int $priority = 100,
        public ?string $kind = null,
    ) {
    }

    /** The frontend component key (defaults to the panel id). */
    public function resolveKind(): string
    {
        return $this->kind ?? $this->id;
    }
}
