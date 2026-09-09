<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

/**
 * Somewhere blocks can be appended to: the body, or a table cell.
 *
 * This is the cursor the Tiptap walk moves around. A mapper that recurses into
 * a cell or a grid column needs to append paragraphs "here" without knowing
 * which "here" it is, and this is that seam.
 *
 * !! Deliberately MUTABLE. Composing a document is an inherently stateful walk
 * of a DOM tree, and a container that returned a new instance on every append
 * would have every mapper threading a return value back up through recursion
 * for no gain. The same reasoning the mapping cursor has always given.
 */
interface BlockContainerInterface
{
    public function appendBlock(BlockInterface $block): void;

    /** @return list<BlockInterface> */
    public function blocks(): array;
}
