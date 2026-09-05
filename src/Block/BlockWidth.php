<?php

declare(strict_types=1);

namespace CoolMS\Core\Block;

/**
 * How much of a row one block occupies.
 *
 * ⚠️ **The gap nobody had named.** Thirteen block types and no notion of "this
 * one takes half a row", so every landing page was a single-column stack. It
 * did not appear in any earlier survey of what blocks were missing, because
 * before a real page was converted every block was full-width by default and
 * the absence of the concept looked like the presence of a default.
 *
 * ⚠️ A FINITE, ABSTRACT SET -- not a column constructor and not a free number.
 * Three reasons, in the order they matter:
 *
 *  1. It is a better editing affordance. "Half" is a choice; "6" is a
 *     question about what the other 6 are.
 *  2. It keeps the vocabulary describable. A theme can say what each of five
 *     widths means in its own grid; it cannot answer for every integer.
 *  3. A class assembled at runtime from a stored value is stripped by a
 *     content-scanning CSS build, silently -- the markup ships and the rule
 *     does not. A declared set is enumerable, so a theme's stylesheet can name
 *     every one of them literally. (Measured here: every theme in this repo
 *     builds on Bootstrap 5.3 with Vite and Sass, and none uses Tailwind, so
 *     that hazard is latent rather than present. It is cheap to stay immune to
 *     it and expensive to retrofit.)
 *
 * ⚠️ Nested rows, per-breakpoint ordering and spacing controls are NOT here.
 * The goal is to stop being a single-column stack, not to become a layout
 * engine -- a page builder that can express any arrangement is one an author
 * can make unreadable, and a theme can no longer promise anything about how a
 * page looks.
 *
 * Fractions of TWELVE, because that is what the grid this platform's themes
 * are built on divides into, and a vocabulary that cannot be expressed in the
 * grid underneath it is a vocabulary that lies.
 */
enum BlockWidth: string
{
    case Full = 'full';

    case TwoThirds = 'two-thirds';

    case Half = 'half';

    case Third = 'third';

    case Quarter = 'quarter';

    /**
     * The width an unset or unrecognised value means.
     *
     * ⚠️ Full, not "narrowest": every block authored before this existed has no
     * width, and they were all full-width. A default that changed their layout
     * would rewrite pages nobody edited.
     */
    public static function default(): self
    {
        return self::Full;
    }

    /**
     * ⚠️ Never throws. A width arrives from stored author data, and an
     * unrecognised one is a page that renders slightly wrong rather than a page
     * that does not render. Same rule the block reader applies to every other
     * stored value.
     */
    public static function fromStored(mixed $value): self
    {
        return is_string($value) ? (self::tryFrom($value) ?? self::default()) : self::default();
    }

    /**
     * Twelfths, for a theme rendering into a twelve-column grid.
     *
     * Exposed as a NUMBER rather than a class name so a theme is free to spend
     * it however its own grid works -- Bootstrap columns, CSS grid spans, or a
     * percentage. The vocabulary is the contract; the class names are not.
     */
    public function twelfths(): int
    {
        return match ($this) {
            self::Full => 12,
            self::TwoThirds => 8,
            self::Half => 6,
            self::Third => 4,
            self::Quarter => 3,
        };
    }

    /** Human label for the block editor's control. */
    public function label(): string
    {
        return match ($this) {
            self::Full => 'Full width',
            self::TwoThirds => 'Two thirds',
            self::Half => 'Half',
            self::Third => 'One third',
            self::Quarter => 'One quarter',
        };
    }
}
