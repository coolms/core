<?php

declare(strict_types=1);
namespace CoolMS\Core\Word;

/**
 * A line or page break.
 *
 * !! Inline, because `w:br` is inline: it belongs INSIDE a run, and the
 * library this replaces wrote it as a direct child of `w:p`, which the schema
 * does not allow (`w:br` is run inner content). Word is forgiving about it;
 * a validating reader is not. Modelling it as an inline is what makes writing
 * it in the wrong place impossible rather than merely unlikely.
 *
 * A page break is therefore a paragraph whose only content is this -- which is
 * also exactly how Word itself represents one.
 */
final readonly class TextBreak implements InlineInterface
{
    public function __construct(
        public BreakKind $kind = BreakKind::Line,
    ) {
    }
}
