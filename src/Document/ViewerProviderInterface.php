<?php

declare(strict_types=1);
namespace CoolMS\Core\Document;

/**
 * Modules contribute viewers (one or more) by tagging an implementation
 * `coolms.viewer_provider`. `ViewerManifestService` aggregates them at
 * boot and the result is exposed in the API manifest under `viewers`.
 *
 * Each module owns its viewers — Pdf ships the PDF viewer, Word ships
 * the DOCX viewer. The Document module is the dispatcher infrastructure
 * (this interface + the aggregator), not a viewer implementation.
 */
interface ViewerProviderInterface
{
    /**
     * @return list<ViewerDefinition>
     */
    public function getViewers(): array;
}
