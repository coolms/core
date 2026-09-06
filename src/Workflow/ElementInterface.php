<?php

declare(strict_types=1);
namespace CoolMS\Core\Workflow;

use CoolMS\Core\Definition\SourceLocation;
use CoolMS\Core\Workflow\ElementKind;
use CoolMS\Core\Workflow\WorkflowAstVisitorInterface;

/**
 * Shared shape for every BPMN-Lite AST element node — the immutable,
 * id-string-linked nodes the parser and the future BPMN XML
 * parser both produce.
 *
 * Cross-element references are id strings (not nested children); see
 * design doc §2.5 for the rationale. {@see $incomingIds} and
 * {@see $outgoingIds} are sequence-flow ids, filled in the parser's
 * second pass once the flow index is built.
 *
 * Boundary events and sequence flows are NOT elements — they live in
 * their own value objects under ``Flow`\` and
 * are dispatched by the visitor separately.
 */
interface ElementInterface
{
    public string $id { get; }
    public ?string $name { get; }
    public ElementKind $kind { get; }

    /** @var list<string> */
    public array $incomingIds { get; }

    /** @var list<string> */
    public array $outgoingIds { get; }

    public ?SourceLocation $sourceLocation { get; }

    public function accept(WorkflowAstVisitorInterface $visitor): void;
}
