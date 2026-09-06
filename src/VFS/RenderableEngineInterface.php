<?php

declare(strict_types=1);
namespace CoolMS\Core\VFS;

/**
 * Implemented by any template/rendering engine that can render VFS file content.
 *
 * Engines self-register via the DI tag `coolms.vfs.renderable_engine`.
 * VFS collects them via RenderableEnginePass and injects them into
 * RenderableExtensionRegistry.
 *
 * VFS Domain knows nothing about DTMPL, Twig, Markdown, or any concrete engine.
 * The interface lives in VFS so that VFS owns the contract.
 */
interface RenderableEngineInterface
{
    /**
     * Extensions that are ALWAYS rendered (isRendered=true by default).
     *
     * When a file with one of these extensions is created, isRendered is set to
     * true automatically -- no manual PATCH required.
     *
     * @return string[] e.g., ['dtmpl']
     */
    public function getAutoRenderedExtensions(): array;

    /**
     * Extensions that CAN be rendered but default to isRendered=false.
     *
     * The user must explicitly set isRendered=true via API PATCH or creation flag.
     *
     * @return string[] e.g., ['html', 'md']
     */
    public function getOptionallyRenderedExtensions(): array;

    /**
     * Priority used when multiple engines register the same extension.
     * Higher value wins. Typical range: 0–100.
     */
    public function getPriority(): int;

    /**
     * Whether this engine is active. An engine may return false to opt out
     * at runtime (e.g., license check, missing library) without unregistering.
     */
    public function isEnabled(): bool;
}
