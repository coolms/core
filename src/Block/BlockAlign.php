<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

/**
 * Where a block sits within its row, when the row is taller than it is.
 *
 * !! **The companion to {@see BlockWidth}, and it was recorded as impossible
 * before it was tried.** The parity pass wrote down that the design centres the
 * hero row's two halves, that this vocabulary aligns their tops, and that
 * nothing could be done because "vertical alignment is a property of the ROW,
 * and the row is the platform's". That reasoning is wrong: in a flex row an
 * ITEM overrides the container with `align-self`, so a block can answer for
 * itself without the row deciding for everyone.
 *
 * The note was written at the moment of hitting the limit, which is before
 * anyone had looked into it -- the same failure this codebase caught twice in
 * one day from the other direction. It is repeated here because a comment
 * saying "this cannot be done" is the kind nobody re-examines.
 *
 * !! PER ITEM, not per row, and that is what makes it expressible at all.
 * Blocks wrap into visual rows; there is no row ELEMENT to address, so "centre
 * this row" cannot be said and "centre this block against whatever it sits
 * beside" can. In practice only the SHORTER block in a pair needs to say it --
 * the taller one already fills the row and centring it is a no-op.
 *
 * !! A finite, abstract set, for the three reasons {@see BlockWidth} gives, and
 * a CLOSED one: these four are the whole of cross-axis alignment rather than a
 * sample of it. There is no fifth to ask for later, which is exactly the
 * property a free-form value would not have.
 */
enum BlockAlign: string
{
    case Top = 'top';

    case Middle = 'middle';

    case Bottom = 'bottom';

    /** Take the full height of the row -- the flex default, named for authors. */
    case Stretch = 'stretch';

    /**
     * The alignment an unset or unrecognised value means.
     *
     * !! Top, not stretch, even though stretch is what flexbox does by default.
     * Every block authored before this existed rendered under
     * `align-items:flex-start`, and a default that changed them would restyle
     * pages nobody edited -- the same rule {@see BlockWidth::default()} follows.
     */
    public static function default(): self
    {
        return self::Top;
    }

    /**
     * !! Never throws. An alignment arrives from stored author data, and an
     * unrecognised one is a page that renders slightly wrong rather than a page
     * that does not render.
     */
    public static function fromStored(mixed $value): self
    {
        return is_string($value) ? (self::tryFrom($value) ?? self::default()) : self::default();
    }

    /**
     * The `align-self` value this means.
     *
     * Exposed so a theme spending the vocabulary on something other than
     * flexbox -- a grid's `align-self`, which takes the same keywords -- has the
     * mapping stated once rather than re-derived per stylesheet.
     */
    public function alignSelf(): string
    {
        return match ($this) {
            self::Top => 'flex-start',
            self::Middle => 'center',
            self::Bottom => 'flex-end',
            self::Stretch => 'stretch',
        };
    }

    /** Human label for the block editor's control. */
    public function label(): string
    {
        return match ($this) {
            self::Top => 'Top',
            self::Middle => 'Middle',
            self::Bottom => 'Bottom',
            self::Stretch => 'Full height',
        };
    }
}
