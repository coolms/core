<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

/**
 * One paragraph: an optional named style, an optional list membership, and the
 * inline content.
 *
 * !! `$styleId` names a style that must EXIST in `word/styles.xml`. A package
 * built from scratch contains only the styles put in it, so a reference to a
 * name Word ships with -- `Quote`, `Heading1` -- is a dangling reference unless
 * the writer defines it, and the paragraph then prints as body text with no
 * error anywhere. That has bitten this pipeline twice: headings once, and
 * blockquotes for as long as they have existed. {@see StylesPart} is where the
 * definitions live, and it is the only place a new `$styleId` may come from.
 *
 * @see `StylesPart`
 */
final class Paragraph implements BlockInterface
{
    /** @var list<InlineInterface> */
    private array $inlines = [];

    /**
     * @param ?ParagraphAlignment $alignment null means UNSTATED, which is not
     *                                       the same as left -- see the enum
     */
    public function __construct(
        public readonly ?string $styleId = null,
        public readonly ?ListKind $listKind = null,
        public readonly int $listLevel = 0,
        public readonly ?ParagraphAlignment $alignment = null,
    ) {
    }

    /** A paragraph whose whole content is a page break, as Word writes one. */
    public static function pageBreak(): self
    {
        $paragraph = new self();
        $paragraph->append(new TextBreak(BreakKind::Page));

        return $paragraph;
    }

    public function append(InlineInterface $inline): void
    {
        $this->inlines[] = $inline;
    }

    /** @return list<InlineInterface> */
    public function inlines(): array
    {
        return $this->inlines;
    }
}
