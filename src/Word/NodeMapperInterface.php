<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

use DOMNode;

/**
 * Strategy contract for translating one DOM node into document-model
 * constructs.
 *
 * Tag implementations with `coolms.document.tiptap_node_mapper`. The
 * RegisterTiptapNodeMapperPass collects them, sorts ascending by `priority()`
 * (lower number = earlier in the list), and feeds the iterable to
 * `TiptapToDocxMapper`.
 *
 * Lower priority on the more specific mappers (gridLayout at 5, formField at 5)
 * so they run before generic block / inline fallbacks (paragraph at 10,
 * span/text at 50). Mappers consult `supports()` and the orchestrator
 * dispatches the first match.
 */
interface NodeMapperInterface
{
    /** Lower runs first. Specific mappers use 5, generic block mappers 10, fallbacks 50. */
    public function priority(): int;

    public function supports(DOMNode $node): bool;

    /**
     * Translate this node onto the current container. The orchestrator is
     * supplied so the mapper can recurse into child nodes (lists into items,
     * gridLayout into columns, etc.).
     */
    public function map(
        DOMNode $node,
        MappingContext $context,
        TiptapNodeMapDispatcherInterface $orchestrator,
    ): void;
}
