<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

use CoolMS\Core\Word\BlockContainerInterface;
use CoolMS\Core\Word\Paragraph;

use function count;

/**
 * The cursor a node mapper appends into.
 *
 * A {@see BlockContainerInterface} is the body or a table cell, so a mapper can
 * recurse into a cell or a grid column without caring which it is.
 *
 * Paragraphs ride a stack: a block mapper opens one, pushes it, child inline
 * mappers append their runs to it, and the pop closes it so the next sibling
 * starts fresh. Block-level mappers ignore the stack and append to
 * {@see currentContainer()}.
 *
 * ## It moved modules, and that is the point
 *
 * This lived in the Document module while every implementation of it lived in
 * Word -- a seam in the wrong place, held together by a cross-module exemption
 * it never needed. It moved so the cursor could carry Word's own document model
 * instead of a vendor library's element tree; the alternative was importing
 * ``Domain`` from ``Domain``, which is a boundary violation in
 * the direction that has no exemption.
 *
 * !! Mutable on purpose. Building a document is an inherently stateful walk of
 * a DOM tree, and copying the cursor on every recurse would obscure more than
 * it would help.
 */
final class MappingContext
{
    private BlockContainerInterface $currentContainer;

    /** @var list<Paragraph> */
    private array $paragraphStack = [];

    public function __construct(BlockContainerInterface $container)
    {
        $this->currentContainer = $container;
    }

    public function currentContainer(): BlockContainerInterface
    {
        return $this->currentContainer;
    }

    /**
     * A cursor pointing into a table cell or a nested column.
     *
     * The paragraph stack deliberately does NOT travel: a cell begins its own
     * block flow, and an inline run started outside it has no business
     * continuing inside.
     */
    public function withContainer(BlockContainerInterface $container): self
    {
        return new self($container);
    }

    public function pushParagraph(Paragraph $paragraph): void
    {
        $this->paragraphStack[] = $paragraph;
    }

    public function popParagraph(): void
    {
        array_pop($this->paragraphStack);
    }

    public function currentParagraph(): ?Paragraph
    {
        $count = count($this->paragraphStack);

        return 0 === $count ? null : $this->paragraphStack[$count - 1];
    }

    /**
     * Somewhere to put a run, opening a paragraph if the walk is not inside
     * one.
     *
     * Inline content can arrive at block level -- a bare text node pasted at
     * the top of a document, an anchor with no paragraph around it -- and it
     * has to go somewhere. A fresh paragraph per stray run reproduces what the
     * previous library did implicitly, and makes the implicit part visible.
     */
    public function paragraphForInline(): Paragraph
    {
        $current = $this->currentParagraph();
        if (null !== $current) {
            return $current;
        }

        $paragraph = new Paragraph();
        $this->currentContainer->appendBlock($paragraph);

        return $paragraph;
    }
}
