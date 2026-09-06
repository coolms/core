<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

use DOMNode;

/**
 * Narrow contract on the orchestrator that node mappers receive. Lets them
 * recurse into child nodes without taking a hard dependency on the
 * Infrastructure-layer `TiptapToDocxMapper` class.
 */
interface TiptapNodeMapDispatcherInterface
{
    public function dispatch(DOMNode $node, MappingContext $context): void;

    /** Iterate children with `dispatch()` applied to each in document order. */
    public function dispatchChildren(DOMNode $node, MappingContext $context): void;
}
