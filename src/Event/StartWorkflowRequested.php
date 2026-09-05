<?php

declare(strict_types=1);

namespace CoolMS\Core\Event;

/**
 * A generic "please start this workflow" integration event.
 *
 * This is the decoupling seam between any module that wants to kick off a BPMN
 * process and the Workflow engine. The *requesting* module (e.g. Content, when a
 * variant is submitted for review) dispatches this with the definition key and
 * variables it owns — it never imports the engine. The Workflow module's generic
 * listener (the workflow module's start-on-request listener)
 * fulfils it via `ProcessStarter::start`, knowing nothing about the requester.
 *
 * Living in Core (L0) keeps the dependency arrows legal in both directions:
 * requesters (L2+) and the engine (L3) each depend only downward on Core. The
 * engine stays domain-agnostic; consumers wire in by dispatching this event.
 *
 * Dispatched synchronously through the Symfony event dispatcher, so the process
 * instance (and its first parked user-task) exists by the time the requesting
 * request returns — modelled on the thin {@see EntityReordered} event.
 */
final readonly class StartWorkflowRequested
{
    /**
     * @param string               $definitionKey       deployed WorkflowDefinition key, e.g. `content.review`
     * @param array<string, mixed> $variables           initial process-variable map
     * @param string|null          $businessKey         optional correlation key (e.g. the subject UUID)
     * @param bool                 $dedupeByBusinessKey opt-in "at most one active instance per business key":
     *                                                  when true AND `$businessKey` is set, the engine skips
     *                                                  the start and returns the existing instance if one is
     *                                                  already active (non-terminal) for this
     *                                                  `(definitionKey, businessKey)`. Off by default, so a
     *                                                  workflow that legitimately allows concurrent instances
     *                                                  per key (e.g. multiple content reviews) is unaffected.
     *                                                  The CDP segment-entry journey seam (Track E Phase 5)
     *                                                  sets it so a subject oscillating in/out of a segment
     *                                                  starts its journey once.
     */
    public function __construct(
        public string $definitionKey,
        public array $variables = [],
        public ?string $businessKey = null,
        public bool $dedupeByBusinessKey = false,
    ) {
    }
}
